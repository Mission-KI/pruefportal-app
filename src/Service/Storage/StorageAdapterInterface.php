<?php
declare(strict_types=1);

namespace App\Service\Storage;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Storage adapter contract for file storage backends
 *
 * Implementations:
 * - S3Adapter: AWS S3 and S3-compatible services (MinIO, Backblaze B2, etc.)
 * - LocalAdapter: Local filesystem storage (private, outside webroot)
 */
interface StorageAdapterInterface
{
    /**
     * Store a file
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The uploaded file
     * @param string $key The storage key/path
     * @param string|null $contentType Optional content type override
     * @return \App\Service\Storage\StoredFile Metadata about the stored file
     * @throws \RuntimeException On storage failure
     */
    public function put(UploadedFileInterface $file, string $key, ?string $contentType = null): StoredFile;

    /**
     * Retrieve a file stream
     *
     * @param string $key The storage key/path
     * @return \App\Service\Storage\StoredFileStream Stream and metadata for download
     * @throws \RuntimeException If file not found
     */
    public function get(string $key): StoredFileStream;

    /**
     * Delete a file
     *
     * @param string $key The storage key/path
     * @throws \RuntimeException On deletion failure
     */
    public function delete(string $key): void;

    /**
     * Check if a file exists
     *
     * @param string $key The storage key/path
     * @return bool True if file exists
     */
    public function exists(string $key): bool;
}
