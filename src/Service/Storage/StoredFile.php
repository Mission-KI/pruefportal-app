<?php
declare(strict_types=1);

namespace App\Service\Storage;

/**
 * Value object representing a stored file's metadata
 *
 * Returned by StorageAdapterInterface::put() after successful upload.
 */
final class StoredFile
{
    /**
     * @param string $key The storage key/path used to retrieve the file
     * @param string $etag Content hash for cache validation
     * @param int $size File size in bytes
     */
    public function __construct(
        public readonly string $key,
        public readonly string $etag,
        public readonly int $size,
    ) {
    }
}
