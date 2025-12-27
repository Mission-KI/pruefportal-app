<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\UploadService;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotImplementedException;
use Cake\Http\Response;
use Cake\Routing\Router;
use Exception;

/**
 * Uploads Controller
 *
 * Handles file uploads, downloads, and management.
 * File storage is delegated to UploadService which supports multiple backends.
 *
 * @property \App\Model\Table\UploadsTable $Uploads
 */
class UploadsController extends AppController
{
    private UploadService $uploadService;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->uploadService = $this->createUploadService();
    }

    /**
     * AJAX endpoint to retrieve upload details by key
     * used in Processes.comments
     * used in UploadsCell.display
     *
     * @param string $key The upload key
     * @return \Cake\Http\Response JSON response with upload details or error
     */
    public function ajaxView(?string $key = null)
    {
        $this->request->allowMethod('ajax');
        $this->response = $this->response->withType('json');

        if (empty($key)) {
            return $this->response->withStatus(400)
                ->withStringBody(json_encode(['error' => 'No upload key provided']));
        }

        $upload = $this->Uploads->findByKey(urldecode($key))->first();

        if (!$upload) {
            return $this->response->withStatus(404)
                ->withStringBody(json_encode(['error' => 'Upload not found']));
        }

        $responseData = [
            'filename' => $upload->name,
            'created' => $upload->created,
            'size' => $upload->size,
            'link' => Router::url(['controller' => 'Uploads', 'action' => 'download', $upload->etag]),
        ];

        return $this->response->withStringBody(json_encode($responseData));
    }

    /**
     * Download a file from storage
     *
     * Streams file directly to response without buffering in memory.
     *
     * @param string $etag The etag of the file to download
     * @return \Cake\Http\Response The response object with file stream
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the file is not found
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to download
     */
    public function download(?string $etag = null): Response
    {
        $upload = $this->Uploads->findByEtag($etag)
            ->contain(['Processes'])
            ->first();

        if (!$upload) {
            throw new RecordNotFoundException('Upload not found');
        }

        // Authorization: user must be participant in the upload's process
        if ($upload->process_id) {
            $userId = $this->request->getAttribute('identity')->id;
            $process = $upload->process;
            if ($process->candidate_user !== $userId && $process->examiner_user !== $userId) {
                throw new ForbiddenException(__('You are not authorized to download this file.'));
            }
        }

        // Get file stream from storage service
        $storedFile = $this->uploadService->download($upload->key);

        // Stream file to response
        return $this->response
            ->withHeader('Content-Type', $storedFile->contentType)
            ->withHeader('Content-Length', (string)$storedFile->contentLength)
            ->withHeader(
                'Content-Disposition',
                sprintf('attachment; filename="%s"', rawurlencode($upload->name ?? $storedFile->filename ?? 'download')),
            )
            ->withBody($storedFile->stream);
    }

    /**
     * Upload file(s) for a process
     *
     * Expected request data:
     * - process_id: int (required)
     * - file: uploaded file (required)
     *
     * @return \Cake\Http\Response JSON response
     */
    public function upload(): Response
    {
        $this->request->allowMethod(['post']);

        $processId = $this->request->getData('process_id');
        $files = $this->request->getUploadedFiles();

        // Handle AJAX requests
        if ($this->request->is('ajax')) {
            $this->response = $this->response->withType('json');

            if (!$processId) {
                return $this->response->withStatus(400)
                    ->withStringBody(json_encode(['error' => 'Missing process_id']));
            }

            if (empty($files) || !isset($files['file'])) {
                return $this->response->withStatus(400)
                    ->withStringBody(json_encode(['error' => 'No file uploaded']));
            }

            $file = $files['file'];

            if ($file->getError() !== UPLOAD_ERR_OK) {
                return $this->response->withStatus(400)
                    ->withStringBody(json_encode(['error' => 'File upload error: ' . $file->getError()]));
            }

            try {
                $upload = $this->uploadService->store(
                    $file,
                    (int)$processId,
                );

                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'key' => $upload->key,
                    'filename' => $file->getClientFilename(),
                ]));
            } catch (Exception $e) {
                return $this->response->withStatus(500)
                    ->withStringBody(json_encode(['error' => $e->getMessage()]));
            }
        }

        // Non-AJAX requests not supported
        return $this->response->withStatus(400)
            ->withStringBody('AJAX requests only');
    }

    /**
     * Download multiple files as ZIP archive
     *
     * TODO: Implement bulk download functionality
     * - Parse upload IDs from query string
     * - Retrieve upload records from database
     * - Download files from storage
     * - Create ZIP archive in memory or temp file
     * - Stream ZIP to user
     * - Clean up temporary files
     *
     * Expected query parameters:
     * - ids: comma-separated list of upload IDs (e.g., "1,2,3")
     *
     * Libraries to consider:
     * - ZipArchive (PHP built-in)
     * - league/flysystem-ziparchive
     *
     * @return \Cake\Http\Response ZIP file download or error
     */
    public function downloadMultiple(): Response
    {
        $this->request->allowMethod(['get']);

        // TODO: Implement bulk download logic
        // $ids = explode(',', $this->request->getQuery('ids', ''));
        // $ids = array_filter(array_map('intval', $ids));
        //
        // if (empty($ids)) {
        //     throw new BadRequestException('No upload IDs provided');
        // }
        //
        // $uploads = $this->Uploads->find()
        //     ->where(['id IN' => $ids])
        //     ->toArray();
        //
        // if (empty($uploads)) {
        //     throw new NotFoundException('No uploads found');
        // }
        //
        // // Create ZIP archive
        // $zip = new \ZipArchive();
        // $zipPath = tempnam(sys_get_temp_dir(), 'uploads_');
        // $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        //
        // foreach ($uploads as $upload) {
        //     $storedFile = $this->uploadService->download($upload->key);
        //     $content = $storedFile->stream->getContents();
        //     $zip->addFromString($upload->name, $content);
        // }
        //
        // $zip->close();
        //
        // $response = $this->response
        //     ->withFile($zipPath)
        //     ->withDownload('uploads.zip');
        //
        // // Clean up temp file after response is sent
        // register_shutdown_function(function() use ($zipPath) {
        //     if (file_exists($zipPath)) {
        //         unlink($zipPath);
        //     }
        // });
        //
        // return $response;

        throw new NotImplementedException(
            'Bulk download functionality not yet implemented. TODO: Create ZIP archive of selected files.',
        );
    }
}
