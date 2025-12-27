<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\UploadService;
use Cake\Http\Response;
use Exception;

/**
 * Uploads Controller
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
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Uploads->find()
            ->contain(['Processes', 'Indicators']);
        $uploads = $this->paginate($query);

        $this->set(compact('uploads'));
    }

    /**
     * View method
     *
     * @param string|null $id Upload id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $upload = $this->Uploads->get($id, contain: ['Processes', 'Indicators']);
        $this->set(compact('upload'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $upload = $this->Uploads->newEmptyEntity();
        if ($this->request->is('post')) {
            $files = $this->request->getUploadedFiles();

            if (!empty($files['file'])) {
                try {
                    $upload = $this->uploadService->store(
                        $files['file'],
                        (int)$this->request->getData('process_id') ?: null,
                        (int)$this->request->getData('comment_id') ?: null,
                        (int)$this->request->getData('indicator_id') ?: null,
                    );
                    $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                    return $this->redirect(['action' => 'index']);
                } catch (Exception $e) {
                    $this->Flash->adminError(__d('admin', 'The entry could not be saved: {0}', $e->getMessage()));
                }
            } else {
                $this->Flash->adminError(__d('admin', 'Please select a file to upload.'));
            }
        }
        $processes = $this->Uploads->Processes->find('list', limit: 200)->all();
        $comments = $this->Uploads->Comments->find('list', limit: 200)->all();
        $indicators = $this->Uploads->Indicators->find('list', limit: 200)->all();
        $this->set(compact('upload', 'processes', 'comments', 'indicators'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Upload id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $upload = $this->Uploads->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $files = $this->request->getUploadedFiles();

            // If a new file is uploaded, replace it
            if (!empty($files['file']) && $files['file']->getError() === UPLOAD_ERR_OK) {
                try {
                    $upload = $this->uploadService->replace($upload, $files['file']);
                    $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                    return $this->redirect(['action' => 'index']);
                } catch (Exception $e) {
                    $this->Flash->adminError(__d('admin', 'The entry could not be saved: {0}', $e->getMessage()));
                }
            } else {
                // Update metadata only (no file change)
                $upload = $this->Uploads->patchEntity($upload, $this->request->getData());
                if ($this->Uploads->save($upload)) {
                    $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
            }
        }
        $processes = $this->Uploads->Processes->find('list', limit: 200)->all();
        $comments = $this->Uploads->Comments->find('list', limit: 200)->all();
        $indicators = $this->Uploads->Indicators->find('list', limit: 200)->all();
        $this->set(compact('upload', 'processes', 'comments', 'indicators'));
    }

    /**
     * Download a file from storage
     *
     * @param string $id The id of the file to download
     * @return \Cake\Http\Response The response object
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the file is not found
     */
    public function download(?string $id = null): Response
    {
        $upload = $this->Uploads->get($id);
        $this->request->allowMethod(['post']);

        $storedFile = $this->uploadService->download($upload->key);

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
     * Delete method
     *
     * @param string|null $id Upload id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $upload = $this->Uploads->get($id);

        if ($this->uploadService->delete($upload)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
