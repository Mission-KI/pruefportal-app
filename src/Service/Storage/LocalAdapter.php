<?php
declare(strict_types=1);

namespace App\Service\Storage;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use function Cake\Core\env;

/**
 * Local filesystem storage adapter
 *
 * Stores files outside the webroot for security (private storage).
 * All file access must go through the controller for authorization.
 *
 * Environment variables:
 * - STORAGE_LOCAL_PATH: Storage directory (default: ROOT/storage/uploads)
 *   Can be relative (resolved from ROOT) or absolute path
 */
class LocalAdapter implements StorageAdapterInterface
{
    private string $basePath;

    /**
     * @param array<string, mixed>|null $config Optional configuration override
     */
    public function __construct(?array $config = null)
    {
        $defaultPath = ROOT . DS . 'storage' . DS . 'uploads';
        $configuredPath = $config['path'] ?? env('STORAGE_LOCAL_PATH', $defaultPath);

        $this->basePath = $this->resolveBasePath($configuredPath);
        $this->ensureDirectoryExists($this->basePath);
    }

    private function resolveBasePath(string $configuredPath): string
    {
        if (!str_starts_with($configuredPath, '/')) {
            return ROOT . DS . $configuredPath;
        }

        return $configuredPath;
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException("Cannot create storage directory: {$path}");
        }
    }

    /**
     * @inheritDoc
     */
    public function put(UploadedFileInterface $file, string $key, ?string $contentType = null): StoredFile
    {
        $fullPath = $this->resolvePath($key);
        $directory = dirname($fullPath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new RuntimeException("Cannot create directory: {$directory}");
        }

        $file->moveTo($fullPath);

        $size = $file->getSize();
        if ($size === null) {
            $size = filesize($fullPath) ?: 0;
        }

        return new StoredFile(
            key: $key,
            etag: md5_file($fullPath) ?: '',
            size: $size,
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): StoredFileStream
    {
        $fullPath = $this->resolvePath($key);

        if (!file_exists($fullPath)) {
            throw new RuntimeException("File not found: {$key}");
        }

        $handle = fopen($fullPath, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Cannot open file: {$key}");
        }

        $size = filesize($fullPath);
        $mimeType = mime_content_type($fullPath);

        return new StoredFileStream(
            stream: new Stream($handle),
            contentType: $mimeType ?: 'application/octet-stream',
            contentLength: $size ?: 0,
            filename: basename($fullPath),
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        $fullPath = $this->resolvePath($key);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $key): bool
    {
        return file_exists($this->resolvePath($key));
    }

    /**
     * Resolve storage key to full filesystem path with security validation
     *
     * @throws \RuntimeException If key contains path traversal attempts
     */
    private function resolvePath(string $key): string
    {
        $this->validateKeyIsSafe($key);

        $normalizedKey = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $key);

        return $this->basePath . DIRECTORY_SEPARATOR . $normalizedKey;
    }

    private function validateKeyIsSafe(string $key): void
    {
        $containsTraversal = str_contains($key, '..');
        $containsNullByte = str_contains($key, "\0");
        $isAbsolutePath = str_starts_with($key, '/');

        if ($containsTraversal || $containsNullByte || $isAbsolutePath) {
            throw new RuntimeException("Invalid storage key: {$key}");
        }
    }
}
