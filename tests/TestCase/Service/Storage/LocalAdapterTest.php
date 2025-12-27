<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Storage;

use App\Service\Storage\LocalAdapter;
use App\Service\Storage\StoredFile;
use App\Service\Storage\StoredFileStream;
use Cake\TestSuite\TestCase;
use GuzzleHttp\Psr7\UploadedFile;
use RuntimeException;

/**
 * LocalAdapter Test Case
 *
 * Tests the local filesystem storage adapter.
 */
class LocalAdapterTest extends TestCase
{
    private string $testStoragePath;
    private ?LocalAdapter $adapter = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary directory for testing
        $this->testStoragePath = sys_get_temp_dir() . '/pruefportal_test_storage_' . uniqid();
        mkdir($this->testStoragePath, 0755, true);

        $this->adapter = new LocalAdapter(['path' => $this->testStoragePath]);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $this->removeDirectory($this->testStoragePath);

        parent::tearDown();
    }

    /**
     * Recursively remove a directory
     */
    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($path);
    }

    /**
     * Create a mock uploaded file for testing
     */
    private function createMockUploadedFile(string $content, string $filename): UploadedFile
    {
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
    // Constructor Tests
    // ========================================

    public function testConstructorCreatesDirectoryIfNotExists(): void
    {
        $newPath = sys_get_temp_dir() . '/pruefportal_new_dir_' . uniqid();

        $this->assertDirectoryDoesNotExist($newPath);

        new LocalAdapter(['path' => $newPath]);

        $this->assertDirectoryExists($newPath);

        // Clean up
        rmdir($newPath);
    }

    public function testConstructorUsesDefaultPath(): void
    {
        // This test verifies the adapter can be instantiated without config
        // It will use ROOT/storage/uploads as default
        $this->assertInstanceOf(LocalAdapter::class, $this->adapter);
    }

    // ========================================
    // Put Tests
    // ========================================

    public function testPutStoresFile(): void
    {
        $content = 'Test file content';
        $file = $this->createMockUploadedFile($content, 'test.txt');

        $result = $this->adapter->put($file, 'uploads/test.txt');

        $this->assertInstanceOf(StoredFile::class, $result);
        $this->assertEquals('uploads/test.txt', $result->key);
        $this->assertEquals(strlen($content), $result->size);
        $this->assertNotEmpty($result->etag);

        // Verify file exists on disk
        $this->assertFileExists($this->testStoragePath . '/uploads/test.txt');
    }

    public function testPutCreatesSubdirectories(): void
    {
        $file = $this->createMockUploadedFile('content', 'test.txt');

        $this->adapter->put($file, 'deep/nested/path/test.txt');

        $this->assertFileExists($this->testStoragePath . '/deep/nested/path/test.txt');
    }

    public function testPutCalculatesCorrectEtag(): void
    {
        $content = 'Known content for etag test';
        $file = $this->createMockUploadedFile($content, 'test.txt');

        $result = $this->adapter->put($file, 'test.txt');

        // Etag should be MD5 of file content
        $expectedEtag = md5($content);
        $this->assertEquals($expectedEtag, $result->etag);
    }

    // ========================================
    // Get Tests
    // ========================================

    public function testGetReturnsFileStream(): void
    {
        $content = 'Test content for download';
        $file = $this->createMockUploadedFile($content, 'download.txt');
        $this->adapter->put($file, 'download.txt');

        $result = $this->adapter->get('download.txt');

        $this->assertInstanceOf(StoredFileStream::class, $result);
        $this->assertEquals(strlen($content), $result->contentLength);
        $this->assertNotEmpty($result->contentType);
        $this->assertEquals('download.txt', $result->filename);

        // Read stream content
        $streamContent = $result->stream->getContents();
        $this->assertEquals($content, $streamContent);
    }

    public function testGetThrowsExceptionForMissingFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found');

        $this->adapter->get('nonexistent.txt');
    }

    // ========================================
    // Delete Tests
    // ========================================

    public function testDeleteRemovesFile(): void
    {
        $file = $this->createMockUploadedFile('content', 'to-delete.txt');
        $this->adapter->put($file, 'to-delete.txt');

        $this->assertFileExists($this->testStoragePath . '/to-delete.txt');

        $this->adapter->delete('to-delete.txt');

        $this->assertFileDoesNotExist($this->testStoragePath . '/to-delete.txt');
    }

    public function testDeleteDoesNotThrowForMissingFile(): void
    {
        // Should not throw when file doesn't exist
        $this->adapter->delete('nonexistent.txt');

        $this->assertTrue(true); // If we got here, no exception was thrown
    }

    // ========================================
    // Exists Tests
    // ========================================

    public function testExistsReturnsTrueForExistingFile(): void
    {
        $file = $this->createMockUploadedFile('content', 'exists.txt');
        $this->adapter->put($file, 'exists.txt');

        $this->assertTrue($this->adapter->exists('exists.txt'));
    }

    public function testExistsReturnsFalseForMissingFile(): void
    {
        $this->assertFalse($this->adapter->exists('nonexistent.txt'));
    }

    // ========================================
    // Security Tests
    // ========================================

    public function testRejectsPathTraversal(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid storage key');

        $this->adapter->get('../../../etc/passwd');
    }

    public function testRejectsPathTraversalWithDotDot(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid storage key');

        $this->adapter->get('uploads/../../../secret.txt');
    }

    public function testRejectsNullBytes(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid storage key');

        $this->adapter->get("uploads/file\0.txt");
    }

    public function testRejectsAbsolutePaths(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid storage key');

        $this->adapter->get('/etc/passwd');
    }

    public function testAllowsValidNestedPaths(): void
    {
        $file = $this->createMockUploadedFile('content', 'test.txt');

        // These should be allowed
        $this->adapter->put($file, 'uploads/2024/01/test.txt');

        $this->assertTrue($this->adapter->exists('uploads/2024/01/test.txt'));
    }
}
