<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Upload;
use App\Service\Storage\StorageAdapterInterface;
use App\Service\Storage\StoredFileStream;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Exception;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Upload orchestration service
 *
 * Handles file upload, download, and deletion with proper transaction handling.
 * Replaces the ORM callback approach for cleaner separation of concerns.
 *
 * Usage in controller:
 * ```php
 * $uploadService = $this->getContainer()->get(UploadService::class);
 * $upload = $uploadService->store($file, $processId);
 * ```
 */
class UploadService
{
    use LocatorAwareTrait;

    public function __construct(
        private StorageAdapterInterface $storage,
    ) {
    }

    /**
     * Store a file and create an Upload record
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The uploaded file
     * @param int|null $processId Associated process
     * @param int|null $commentId Associated comment
     * @param int|null $indicatorId Associated indicator
     * @return \App\Model\Entity\Upload The created upload entity
     * @throws \Exception On upload or save failure
     */
    public function store(
        UploadedFileInterface $file,
        ?int $processId = null,
        ?int $commentId = null,
        ?int $indicatorId = null,
    ): Upload {
        $uploadsTable = $this->fetchTable('Uploads');

        $originalFilename = $file->getClientFilename() ?? 'unnamed';
        $key = $this->generateKey($originalFilename);
        $stored = $this->storage->put($file, $key);

        $upload = $uploadsTable->newEntity([
            'key' => $stored->key,
            'name' => $this->truncateFilename($originalFilename),
            'size' => $stored->size,
            'location' => $key,
            'etag' => $stored->etag,
            'process_id' => $processId,
            'comment_id' => $commentId,
            'indicator_id' => $indicatorId,
        ]);

        if (!$uploadsTable->save($upload)) {
            $this->rollbackStorageUpload($key);
            throw new Exception('Failed to save upload record: ' . json_encode($upload->getErrors()));
        }

        return $upload;
    }

    /**
     * Replace an existing upload's file
     *
     * @param \App\Model\Entity\Upload $upload Existing upload to replace
     * @param \Psr\Http\Message\UploadedFileInterface $file New file
     * @return \App\Model\Entity\Upload Updated upload entity
     * @throws \Exception On upload or save failure
     */
    public function replace(Upload $upload, UploadedFileInterface $file): Upload
    {
        $uploadsTable = $this->fetchTable('Uploads');
        $oldKey = $upload->key;

        $originalFilename = $file->getClientFilename() ?? 'unnamed';
        $newKey = $this->generateKey($originalFilename);
        $stored = $this->storage->put($file, $newKey);

        $upload->key = $stored->key;
        $upload->name = $this->truncateFilename($originalFilename);
        $upload->size = $stored->size;
        $upload->location = $newKey;
        $upload->etag = $stored->etag;

        if (!$uploadsTable->save($upload)) {
            $this->rollbackStorageUpload($newKey);
            throw new Exception('Failed to update upload record: ' . json_encode($upload->getErrors()));
        }

        $this->deleteOrphanedFile($oldKey);

        return $upload;
    }

    /**
     * Get file stream for download
     *
     * @param string $key Storage key
     * @return \App\Service\Storage\StoredFileStream Stream and metadata for download
     */
    public function download(string $key): StoredFileStream
    {
        return $this->storage->get($key);
    }

    /**
     * Delete an upload and its file
     *
     * Deletes DB record first, then storage file.
     * If storage deletion fails, file becomes orphan (logged but not fatal).
     *
     * @param \App\Model\Entity\Upload $upload The upload to delete
     * @return bool True if DB record was deleted
     */
    public function delete(Upload $upload): bool
    {
        $uploadsTable = $this->fetchTable('Uploads');
        $key = $upload->key;

        if (!$uploadsTable->delete($upload)) {
            return false;
        }

        $this->deleteOrphanedFile($key);

        return true;
    }

    /**
     * Check if a file exists in storage
     *
     * @param string $key Storage key
     * @return bool True if file exists
     */
    public function exists(string $key): bool
    {
        return $this->storage->exists($key);
    }

    /**
     * Generate a unique storage key
     *
     * Format: uploads/{timestamp}_{truncated_filename}
     */
    private function generateKey(string $filename): string
    {
        $truncated = $this->truncateFilename($filename);

        return sprintf('uploads/%d_%s', time(), $truncated);
    }

    /**
     * Truncate filename to safe length while preserving extension
     *
     * @param string $filename Original filename
     * @param int $maxLength Maximum length (default: 80)
     * @return string Truncated filename
     */
    private function truncateFilename(string $filename, int $maxLength = 80): string
    {
        if (strlen($filename) <= $maxLength) {
            return $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $maxBasename = $maxLength - strlen($extension) - 1;

        return substr($basename, 0, $maxBasename) . '.' . $extension;
    }

    private function rollbackStorageUpload(string $key): void
    {
        try {
            $this->storage->delete($key);
        } catch (Exception $e) {
            Log::warning("Failed to rollback uploaded file after DB error: {$key}");
        }
    }

    private function deleteOrphanedFile(?string $key): void
    {
        if ($key === null) {
            return;
        }

        try {
            $this->storage->delete($key);
        } catch (Exception $e) {
            Log::warning("Failed to delete orphaned storage file: {$key}");
        }
    }
}
