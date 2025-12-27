<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Upload;
use App\Service\Storage\StorageAdapterInterface;
use App\Service\Storage\StoredFile;
use App\Service\Storage\StoredFileStream;
use App\Service\UploadService;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Exception;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\UploadedFile;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * UploadService Test Case
 *
 * Tests the upload orchestration service.
 */
class UploadServiceTest extends TestCase
{
    use LocatorAwareTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.Uploads',
        'app.Comments',
        'app.Indicators',
    ];

    private UploadService $service;
    private MockObject $mockStorage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock storage adapter
        $this->mockStorage = $this->createMock(StorageAdapterInterface::class);

        $this->service = new UploadService($this->mockStorage);

        // Reset the sequence to avoid primary key conflicts with fixtures
        $connection = $this->fetchTable('Uploads')->getConnection();
        $connection->execute("SELECT setval('uploads_id_seq', (SELECT COALESCE(MAX(id), 0) + 1 FROM uploads), false)");
    }

    /**
     * Create a mock uploaded file for testing
     */
    private function createMockUploadedFile(
        string $content = 'test content',
        string $filename = 'test.txt',
    ): UploadedFile {
        $tempFile = tempnam(sys_get_temp_dir(), 'upload_test_');
        file_put_contents($tempFile, $content);

        return new UploadedFile(
            $tempFile,
            strlen($content),
            UPLOAD_ERR_OK,
            $filename,
            'application/octet-stream',
        );
    }

    // ========================================
    // Store Tests
    // ========================================

    public function testStoreUploadsFileAndCreatesRecord(): void
    {
        $file = $this->createMockUploadedFile('test content', 'document.pdf');

        $this->mockStorage
            ->expects($this->once())
            ->method('put')
            ->willReturn(new StoredFile(
                key: 'uploads/123456_document.pdf',
                etag: 'abc123',
                size: 12,
            ));

        $upload = $this->service->store($file, processId: 1);

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertEquals('uploads/123456_document.pdf', $upload->key);
        $this->assertEquals('document.pdf', $upload->name);
        $this->assertEquals(12, $upload->size);
        $this->assertEquals('abc123', $upload->etag);
        $this->assertEquals(1, $upload->process_id);
        $this->assertNotNull($upload->id);
    }

    public function testStoreWithAllAssociations(): void
    {
        $file = $this->createMockUploadedFile();

        $this->mockStorage
            ->method('put')
            ->willReturn(new StoredFile(
                key: 'uploads/test.txt',
                etag: 'etag',
                size: 12,
            ));

        $upload = $this->service->store(
            $file,
            processId: 1,
            commentId: null,
            indicatorId: null,
        );

        $this->assertEquals(1, $upload->process_id);
    }

    public function testStoreRollsBackOnDatabaseError(): void
    {
        $file = $this->createMockUploadedFile();

        // Storage succeeds
        $this->mockStorage
            ->expects($this->once())
            ->method('put')
            ->willReturn(new StoredFile(
                key: 'uploads/test.txt',
                etag: 'etag',
                size: 12,
            ));

        // Expect rollback delete when DB save fails
        $this->mockStorage
            ->expects($this->once())
            ->method('delete')
            ->with($this->stringContains('uploads/'));

        // Force DB error by using invalid process_id
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to save upload record');

        $this->service->store($file, processId: 99999);
    }

    public function testStoreTruncatesLongFilenames(): void
    {
        $longFilename = str_repeat('a', 100) . '.pdf';
        $file = $this->createMockUploadedFile('content', $longFilename);

        $this->mockStorage
            ->method('put')
            ->willReturn(new StoredFile(
                key: 'uploads/truncated.pdf',
                etag: 'etag',
                size: 7,
            ));

        $upload = $this->service->store($file, processId: 1);

        // Name should be truncated to 80 chars
        $this->assertLessThanOrEqual(80, strlen($upload->name));
    }

    // ========================================
    // Replace Tests
    // ========================================

    public function testReplaceUploadsNewFileAndDeletesOld(): void
    {
        // Get existing upload from fixture
        $uploadsTable = $this->fetchTable('Uploads');
        $existingUpload = $uploadsTable->get(1);
        $oldKey = $existingUpload->key;

        $newFile = $this->createMockUploadedFile('new content', 'replacement.pdf');

        $this->mockStorage
            ->expects($this->once())
            ->method('put')
            ->willReturn(new StoredFile(
                key: 'uploads/new_replacement.pdf',
                etag: 'newetag',
                size: 11,
            ));

        $this->mockStorage
            ->expects($this->once())
            ->method('delete')
            ->with($oldKey);

        $updated = $this->service->replace($existingUpload, $newFile);

        $this->assertEquals('uploads/new_replacement.pdf', $updated->key);
        $this->assertEquals('replacement.pdf', $updated->name);
        $this->assertEquals('newetag', $updated->etag);
    }

    // ========================================
    // Download Tests
    // ========================================

    public function testDownloadReturnsStream(): void
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('file content');
        $stream->rewind();

        $this->mockStorage
            ->expects($this->once())
            ->method('get')
            ->with('uploads/test.txt')
            ->willReturn(new StoredFileStream(
                stream: $stream,
                contentType: 'text/plain',
                contentLength: 12,
                filename: 'test.txt',
            ));

        $result = $this->service->download('uploads/test.txt');

        $this->assertInstanceOf(StoredFileStream::class, $result);
        $this->assertEquals('text/plain', $result->contentType);
        $this->assertEquals(12, $result->contentLength);
    }

    // ========================================
    // Delete Tests
    // ========================================

    public function testDeleteRemovesRecordAndFile(): void
    {
        $uploadsTable = $this->fetchTable('Uploads');
        $upload = $uploadsTable->get(1);
        $key = $upload->key;

        $this->mockStorage
            ->expects($this->once())
            ->method('delete')
            ->with($key);

        $result = $this->service->delete($upload);

        $this->assertTrue($result);
        $this->assertNull($uploadsTable->find()->where(['id' => 1])->first());
    }

    public function testDeleteSucceedsEvenIfStorageDeleteFails(): void
    {
        $uploadsTable = $this->fetchTable('Uploads');
        $upload = $uploadsTable->get(1);

        $this->mockStorage
            ->method('delete')
            ->willThrowException(new Exception('Storage error'));

        // Should still return true because DB record was deleted
        $result = $this->service->delete($upload);

        $this->assertTrue($result);
    }

    // ========================================
    // Exists Tests
    // ========================================

    public function testExistsChecksStorage(): void
    {
        $this->mockStorage
            ->expects($this->once())
            ->method('exists')
            ->with('uploads/test.txt')
            ->willReturn(true);

        $this->assertTrue($this->service->exists('uploads/test.txt'));
    }

    public function testExistsReturnsFalseForMissingFile(): void
    {
        $this->mockStorage
            ->method('exists')
            ->willReturn(false);

        $this->assertFalse($this->service->exists('nonexistent.txt'));
    }
}
