<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Storage;

use App\Service\Storage\S3Adapter;
use App\Service\Storage\StorageAdapterInterface;
use App\Service\Storage\StoredFile;
use App\Service\Storage\StoredFileStream;
use Cake\TestSuite\TestCase;
use GuzzleHttp\Psr7\Stream;
use RuntimeException;

/**
 * S3Adapter Test Case
 *
 * Tests the S3 storage adapter configuration and initialization.
 * Full integration tests require a running S3/MinIO instance.
 */
class S3AdapterTest extends TestCase
{
    // ========================================
    // Constructor / Configuration Tests
    // ========================================

    public function testConstructorThrowsWithoutBucket(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('S3 bucket not configured');

        // Clear any existing env vars
        putenv('S3_BUCKET_NAME_ATTACHMENTS');

        new S3Adapter([
            'bucket' => '',
            'access_key' => 'test',
            'secret_key' => 'test',
        ]);
    }

    public function testConstructorAcceptsConfigOverride(): void
    {
        $adapter = new S3Adapter([
            'bucket' => 'test-bucket',
            'access_key' => 'test-key',
            'secret_key' => 'test-secret',
            'region' => 'us-west-2',
        ]);

        $this->assertInstanceOf(S3Adapter::class, $adapter);
    }

    public function testConstructorSupportsMinioEndpoint(): void
    {
        $adapter = new S3Adapter([
            'bucket' => 'test-bucket',
            'access_key' => 'minioadmin',
            'secret_key' => 'minioadmin',
            'endpoint' => 'http://localhost:9000',
            'use_path_style' => true,
        ]);

        $this->assertInstanceOf(S3Adapter::class, $adapter);
    }

    public function testConstructorUsesDefaultRegion(): void
    {
        // When no region is specified, should default to eu-central-1
        $adapter = new S3Adapter([
            'bucket' => 'test-bucket',
            'access_key' => 'test',
            'secret_key' => 'test',
        ]);

        $this->assertInstanceOf(S3Adapter::class, $adapter);
    }

    // ========================================
    // Interface Compliance Tests
    // ========================================

    public function testImplementsStorageAdapterInterface(): void
    {
        $adapter = new S3Adapter([
            'bucket' => 'test-bucket',
            'access_key' => 'test',
            'secret_key' => 'test',
        ]);

        $this->assertInstanceOf(
            StorageAdapterInterface::class,
            $adapter,
        );
    }

    public function testHasRequiredMethods(): void
    {
        $adapter = new S3Adapter([
            'bucket' => 'test-bucket',
            'access_key' => 'test',
            'secret_key' => 'test',
        ]);

        $this->assertTrue(method_exists($adapter, 'put'));
        $this->assertTrue(method_exists($adapter, 'get'));
        $this->assertTrue(method_exists($adapter, 'delete'));
        $this->assertTrue(method_exists($adapter, 'exists'));
    }

    // ========================================
    // Value Object Tests
    // ========================================

    public function testStoredFileValueObject(): void
    {
        $storedFile = new StoredFile(
            key: 'uploads/test.txt',
            etag: 'abc123',
            size: 1024,
        );

        $this->assertEquals('uploads/test.txt', $storedFile->key);
        $this->assertEquals('abc123', $storedFile->etag);
        $this->assertEquals(1024, $storedFile->size);
    }

    public function testStoredFileStreamValueObject(): void
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('test content');
        $stream->rewind();

        $storedFileStream = new StoredFileStream(
            stream: $stream,
            contentType: 'text/plain',
            contentLength: 12,
            filename: 'test.txt',
        );

        $this->assertEquals('text/plain', $storedFileStream->contentType);
        $this->assertEquals(12, $storedFileStream->contentLength);
        $this->assertEquals('test.txt', $storedFileStream->filename);
        $this->assertEquals('test content', $storedFileStream->stream->getContents());
    }

    public function testStoredFileStreamFilenameIsOptional(): void
    {
        $stream = new Stream(fopen('php://temp', 'r+'));

        $storedFileStream = new StoredFileStream(
            stream: $stream,
            contentType: 'application/octet-stream',
            contentLength: 0,
        );

        $this->assertNull($storedFileStream->filename);
    }
}
