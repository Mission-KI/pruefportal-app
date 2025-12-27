<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\UploadService;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\ForbiddenException;
use Exception;

/**
 * UsecaseDescriptions Controller
 *
 * TODO refactor the redundant code of add and edit
 *
 * @property \App\Model\Table\UsecaseDescriptionsTable $UsecaseDescriptions
 */
class UsecaseDescriptionsController extends AppController
{
    use ModelAwareTrait;

    private UploadService $uploadService;

    public function initialize(): void
    {
        parent::initialize();
        $this->uploadService = $this->createUploadService();
    }

    /**
     * Add method
     *
     * Auto-creates a draft entity immediately on GET request and redirects to edit.
     * This ensures the entity has an ID for auto-save and save-draft functionality.
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Redirects to edit or renders form view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to add.
     */
    public function add(?string $process_id = null)
    {
        $process = $this->UsecaseDescriptions->Processes->get($process_id, contain: []);

        if ($process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        if ($this->request->is('get')) {
            $usecaseDescription = $this->UsecaseDescriptions->newEmptyEntity();
            $usecaseDescription->process_id = $process_id;
            $usecaseDescription->version = 1;
            $usecaseDescription->user_id = $this->Authentication->getIdentity()->id;
            $usecaseDescription->step = 0;
            $usecaseDescription->status = 'draft';
            $usecaseDescription->description = json_encode([]);

            if ($this->UsecaseDescriptions->save($usecaseDescription)) {
                return $this->redirect(['action' => 'edit', $usecaseDescription->id]);
            }

            $this->Flash->error(__('Could not create draft. Please try again.'));

            return $this->redirect(['controller' => 'Projects', 'action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $usecaseDescription = $this->UsecaseDescriptions->newEmptyEntity();
            $usecaseDescription = $this->UsecaseDescriptions->patchEntity($usecaseDescription, $this->request->getData());
            $usecaseDescription->process_id = $process_id;
            $usecaseDescription->version = 1;
            $usecaseDescription->user_id = $this->Authentication->getIdentity()->id;
            $ucdJSON = $this->request->getData('ucd');

            $files = $this->request->getUploadedFiles();
            if (!empty($files['ucd'])) {
                foreach ($files['ucd'] as $i => $file) {
                    $upload = $this->uploadService->store($file, (int)$process_id);
                    if ($upload->key !== '') {
                        $ucdJSON[$i] = $upload->key;
                    }
                }
            }
            $usecaseDescription->description = json_encode($ucdJSON);

            if ($this->UsecaseDescriptions->save($usecaseDescription)) {
                $this->Flash->success(__('The current UCD Step has been saved.'));

                return $this->redirect(['action' => 'edit', $usecaseDescription->id]);
            }
            $this->Flash->error(__('The current UCD Step could not be saved. Please, try again.'));
        }

        $usecaseDescription = $this->UsecaseDescriptions->newEmptyEntity();
        $this->set(compact('usecaseDescription', 'process'));
        $this->render('form');
    }

    /**
     * Edit method
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null|void Renders form view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to edit.
     */
    public function edit(?string $id = null)
    {
        $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: ['Processes']);
        $process = $usecaseDescription->process;
        if ($process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        if ($usecaseDescription->status === 'submitted') {
            $this->Flash->error(__('Cannot edit a submitted use case description.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $requestData = $this->request->getData();
            $currentDbStep = $usecaseDescription->step;
            $submittedStep = isset($requestData['step']) ? (int)$requestData['step'] : $currentDbStep;

            // Only update step if moving forward (submitted step > current DB step)
            if ($submittedStep > $currentDbStep) {
                $usecaseDescription->step = $submittedStep;
            }

            // Patch other fields (excluding step to prevent unconditional update)
            unset($requestData['step']);
            $usecaseDescription = $this->UsecaseDescriptions->patchEntity($usecaseDescription, $requestData);

            $ucdJSON = $this->request->getData('ucd', []);

            $existingData = $usecaseDescription->getParsedDescription();

            $mergedData = array_merge($existingData, $ucdJSON);

            $files = $this->request->getUploadedFiles();
            if (!empty($files['ucd'])) {
                foreach ($files['ucd'] as $fieldName => $file) {
                    if ($file->getSize() > 0) {
                        $upload = $this->uploadService->store($file, (int)$process->id);
                        if ($upload->key !== '') {
                            $mergedData[$fieldName] = $upload->key;
                        }
                    }
                }
            }

            $usecaseDescription->description = json_encode($mergedData);

            if ((int)$usecaseDescription->step === (int)$this->request->getData('maxSteps')) {
                $usecaseDescription->status = 'submitted';
            }

            if ($this->UsecaseDescriptions->save($usecaseDescription)) {
                if ((int)$usecaseDescription->step === (int)$this->request->getData('maxSteps')) {
                    $process->status_id = 20;
                    if (!$this->UsecaseDescriptions->Processes->save($process)) {
                        $this->Flash->error(__('Process status could not be updated. Please contact support.'));

                        return $this->redirect(['controller' => 'Processes', 'action' => 'view', $process->id]);
                    }

                    $notificationModel = $this->fetchModel('Notifications');
                    $desc = __('The UCD for the process "{0}" is completed by the candidate. Please review the UCD.', $process->title);

                    if (!empty($process->examiners)) {
                        foreach ($process->examiners as $examiner) {
                            $notificationModel->createNotification(
                                __('Notification: UCD Completed'),
                                $desc,
                                $examiner->id,
                                $process->id,
                            );
                        }
                    }

                    $this->Flash->success(__('Use case description submitted successfully.'));

                    return $this->redirect(['controller' => 'Processes', 'action' => 'view', $process->id]);
                } else {
                    $this->Flash->success(__('The current UCD Step has been saved.'));
                    // Redirect to next step in UI flow (submittedStep + 1)
                    $nextStep = $submittedStep + 1;

                    return $this->redirect(['action' => 'edit', $usecaseDescription->id, '?' => ['step' => $nextStep]]);
                }
            }
            $this->Flash->error(__('The current UCD Step could not be saved. Please, try again.'));
        }

        $serverData = [];
        if (!$usecaseDescription->isNew()) {
            $serverData = $usecaseDescription->getParsedDescription();
        }

        $this->set(compact('usecaseDescription', 'process', 'serverData'));
        $this->render('form');
    }

    /**
     * View method
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null|void Renders view view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to view.
     */
    public function view(?string $id = null)
    {
        $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: ['Processes' => ['Examiners']]);
        $process = $usecaseDescription->process;
        $query = $this->UsecaseDescriptions->Processes->Comments->find('list', keyField: 'id', valueField: 'reference_id', conditions: ['process_id' => $process->id]);
        $commentReferences = $query->toArray();
        $userId = $this->request->getAttribute('identity')->id;
        if (
            $process->candidate_user !== $userId &&
            !$process->isUserExaminer($userId)
        ) {
            throw new ForbiddenException();
        }
        $this->set(compact('usecaseDescription', 'process', 'commentReferences'));
    }

    /**
     * Review method
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null|void Renders review view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to review.
     */
    public function review(?string $id = null)
    {
        $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: ['Processes' => ['Examiners']]);
        $process = $usecaseDescription->process;
        $query = $this->UsecaseDescriptions->Processes->Comments->find('list', keyField: 'id', valueField: 'reference_id', conditions: ['process_id' => $process->id]);
        $commentReferences = $query->toArray();
        if (!$process->isUserExaminer($this->request->getAttribute('identity')->id)) {
            throw new ForbiddenException();
        }
        $mode = 'review';
        $this->set(compact('usecaseDescription', 'process', 'commentReferences', 'mode'));
        $this->render('view');
    }

    /**
     * Accept method
     *
     * Marks the UCD as accepted and notifies the candidate.
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null Redirects to index on successful accept, throws ForbiddenException on invalid request.
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to accept.
     */
    public function accept(?string $id = null)
    {
        if ($this->request->is('post')) {
            $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: ['Processes' => ['Examiners']]);
            $process = $usecaseDescription->process;

            if (!$process->isUserExaminer($this->request->getAttribute('identity')->id)) {
                throw new ForbiddenException();
            }

            // 1) Set the status of the process
            $process->status_id = 20; // $this->statuses

            // 2) Create a notification
            $notificationModel = $this->fetchModel('Notifications');
            $desc = __('The UCD for the process "{0}" has been accepted by the examiner. Please wait for the Analysis of the Protection Needs.', $process->title);
            $notificationModel->createNotification(__('Notification: UCD Accepted'), $desc, $process->candidate_user, $process->id);

            if ($this->UsecaseDescriptions->Processes->save($process)) {
                $this->Flash->success(__('The UCD has been marked as accepted and the candidate will be notified.'));
            } else {
                $this->Flash->error(__('The UCD could not be marked as accepted. Please, try again.'));
            }

            return $this->redirect('/');
        } else {
            throw new ForbiddenException();
        }
    }

    /**
     * Reject method
     *
     * @var \App\Model\Entity\Notification $notification
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null|void Redirects to index
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to reject.
     */
    public function reject(?string $id = null)
    {
        if ($this->request->is('post')) {
            $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: ['Processes' => ['Examiners']]);
            $process = $usecaseDescription->process;

            if (!$process->isUserExaminer($this->request->getAttribute('identity')->id)) {
                throw new ForbiddenException();
            }

            // 1) Create a notification
            $notificationModel = $this->fetchModel('Notifications');
            $desc = __('The UCD for the process "{0}" has been rejected by the examiner. Please review the UCD.', $process->title);
            $notificationModel->createNotification(__('Notification: UCD Rejected'), $desc, $process->candidate_user, $process->id);

            // 2) Set the status of the process back
            $process->status_id = 10; // $this->statuses

            if ($this->UsecaseDescriptions->Processes->save($process)) {
                // 3) Clone the UsecaseDescription and increment the version
                $newUsecaseDescription = $this->UsecaseDescriptions->newEmptyEntity();
                $newUsecaseDescriptionData = $usecaseDescription->toArray();

                unset($newUsecaseDescriptionData['id']); // because it is a new entity
                unset($newUsecaseDescriptionData['process']); // to prevent a duplicate process entry

                $newUsecaseDescriptionData['version']++; // increment the version
                $newUsecaseDescriptionData['step'] = 1; // reset the step
                $newUsecaseDescriptionData['process_id'] = $usecaseDescription->process_id;
                $newUsecaseDescription = $this->UsecaseDescriptions->patchEntity($newUsecaseDescription, $newUsecaseDescriptionData);
                $this->UsecaseDescriptions->save($newUsecaseDescription);

                // 4) Set the step of the rejected UsecaseDescription to -1
                $usecaseDescription->step = -1;
                $this->UsecaseDescriptions->save($usecaseDescription);

                $this->Flash->success(__('The UCD has been marked as rejected and the candidate will be notified.'));
            } else {
                $this->Flash->error(__('The UCD could not be rejected. Please, try again.'));
            }

            return $this->redirect('/');
        } else {
            throw new ForbiddenException();
        }
    }

    /**
     * Save Draft method
     *
     * @param int|null $id Usecase Description id.
     * @return \Cake\Http\Response JSON response with success status and timestamp
     */
    public function saveDraft(?int $id = null)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $usecaseDescription = $this->UsecaseDescriptions->get($id, [
            'contain' => ['Processes'],
        ]);

        $identity = $this->Authentication->getIdentity();
        if ($usecaseDescription->process->candidate_user !== $identity->id) {
            return $this->response
                ->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Not authorized',
                ]));
        }

        if ($usecaseDescription->status !== 'draft') {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Cannot modify submitted UCD',
                ]));
        }

        $incomingData = $this->request->getData('ucd', []);

        if (!is_array($incomingData)) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Invalid ucd data format',
                ]));
        }

        $existingData = $usecaseDescription->getParsedDescription();

        $mergedData = array_merge($existingData, $incomingData);

        $isExplicitSave = $this->request->getData('explicit_save', false);
        if ($isExplicitSave) {
            $files = $this->request->getUploadedFiles()['ucd'] ?? [];
            if (!empty($files)) {
                foreach ($files as $fieldName => $file) {
                    if ($file->getSize() > 0) {
                        try {
                            $upload = $this->uploadService->store(
                                $file,
                                (int)$usecaseDescription->process_id,
                            );
                            $mergedData[$fieldName] = $upload->key;
                        } catch (Exception $e) {
                            return $this->response
                                ->withType('application/json')
                                ->withStatus(500)
                                ->withStringBody(json_encode([
                                    'success' => false,
                                    'error' => 'File upload failed',
                                ]));
                        }
                    }
                }
            }
        }

        $usecaseDescription->description = json_encode($mergedData);

        if ($this->UsecaseDescriptions->save($usecaseDescription)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'timestamp' => date('H:i:s'),
                ]));
        }

        return $this->response
            ->withType('application/json')
            ->withStatus(500)
            ->withStringBody(json_encode([
                'success' => false,
                'error' => 'Failed to save',
            ]));
    }
}
