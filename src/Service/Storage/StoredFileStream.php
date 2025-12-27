<?php
declare(strict_types=1);

namespace App\Service\Storage;

use Psr\Http\Message\StreamInterface;

/**
 * Value object for file download with stream
 *
 * Returned by StorageAdapterInterface::get() for streaming downloads.
 * The stream should be passed directly to the HTTP response body.
 */
final class StoredFileStream
{
    /**
     * @param \Psr\Http\Message\StreamInterface $stream PSR-7 stream for reading file content
     * @param string $contentType MIME type of the file
     * @param int $contentLength File size in bytes
     * @param string|null $filename Original filename if available
     */
    public function __construct(
        public readonly StreamInterface $stream,
        public readonly string $contentType,
        public readonly int $contentLength,
        public readonly ?string $filename = null,
    ) {
    }
}
