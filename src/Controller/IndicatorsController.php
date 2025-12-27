<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Process;
use App\Service\UploadService;
use Authentication\Identity;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use RuntimeException;

/**
 * Indicators Controller
 *
 * @property \App\Model\Table\IndicatorsTable $Indicators
 */
class IndicatorsController extends AppController
{
    use ModelAwareTrait;

    private array $vcioConfig;
    private array $shortTitles;
    private UploadService $uploadService;

    /**
     * Initialization hook method.
     *
     * Called when the controller is instantiated. Invoked after the controller's
     * constructor and before it processes any request data.
     *
     * Sets the protection needs analysis config and the quality dimensions for the view.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->vcioConfig = $this->Indicators->getVcioConfig();
        $this->set(['vcioConfig' => $this->vcioConfig]);

        $this->shortTitles = $this->getShortTitles($this->vcioConfig);
        $this->set('shortTitles', $this->shortTitles);

        $this->uploadService = $this->createUploadService();
    }

    public function index($process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id);
        if (!$process || $process->candidate_user !== $this->request->getAttribute('identity')->id || $process->status_id < 30) { // $this->statuses
            throw new ForbiddenException();
        }

        // Check if for this quality_dimension_id $indicators exists
        $indicators = $this->Indicators->find('list', keyField: 'quality_dimension_id', valueField: 'id')
            ->where(['process_id' => $process->id])
            ->distinct(['Indicators.quality_dimension_id'])
            ->toArray();

        $this->set(compact('process', 'indicators'));
    }

    /**
     * Add method
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders form view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to add.
     */
    public function add(?string $process_id = null, $qualityDimension = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: []);
        if (!$process || $process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        // Only allow adding during VCIO phase (status 30)
        if ($process->status_id !== 30) {
            throw new ForbiddenException('VCIO phase not active');
        }

        if (!$qualityDimension) {
            $qualityDimension = array_key_first($this->vcioConfig);
        }

        if (!isset($this->vcioConfig[$qualityDimension])) {
            throw new NotFoundException('Invalid quality dimension');
        }

        // Check if version=0 indicators already exist for this QD (only redirect on GET, not POST)
        $quality_dimension_id = $this->vcioConfig[$qualityDimension]['quality_dimension_id'];
        if (!$this->request->is('post')) {
            $existingCount = $this->Indicators->find()->where([
                'process_id' => $process_id,
                'quality_dimension_id' => $quality_dimension_id,
                'version' => 0,
            ])->count();

            if ($existingCount > 0) {
                // Redirect to edit instead
                return $this->redirect(['action' => 'edit', $process_id, $qualityDimension]);
            }
        }

        // Indicators relating to a CriterionType that are not relevant according to the Criteria are hidden.
        $relevantCriterionTypes = $this->Indicators->getCriterionTypeIds($this->vcioConfig, $qualityDimension);
        $criteriaModel = $this->fetchModel('Criteria');
        $relevances = [];
        foreach ($relevantCriterionTypes as $criterionTypeId) {
            $relevances[$criterionTypeId] = current($criteriaModel->calculateRelevanceByCriterionTypeId($process_id, $criterionTypeId));
        }

        $indicators = $this->Indicators->find('list', keyField: 'quality_dimension_id', valueField: 'id')
            ->where(['process_id' => $process->id])
            ->distinct(['Indicators.quality_dimension_id'])
            ->toArray();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Check for version=0 indicators (auto-save may have created them)
            $existingIndicators = $this->Indicators->find()->where([
                'process_id' => $process_id,
                'quality_dimension_id' => $quality_dimension_id,
                'version' => 0,
            ])->all();

            if ($existingIndicators->count() > 0) {
                // UPDATE path (most common with auto-save)
                $entities = $this->Indicators->patchEntities($existingIndicators, $data['indicators']);
            } else {
                // INSERT path (fallback if JS disabled/offline)
                $entities = $this->Indicators->newEntities($data['indicators']);
            }

            $result = $this->Indicators->saveMany($entities);

            if ($result) {
                // saveMany returns an array of entities with the id set
                $files = $this->request->getUploadedFiles();
                if (!empty($files['indicators'])) {
                    // Handle attachments
                    foreach ($files['indicators'] as $i => $files) {
                        $indicator_id = array_key_exists($i, $result) ? $result[$i]['id'] : null;
                        if (array_key_exists('attachments', $files) && count($files['attachments']) > 0) {
                            foreach ($files['attachments'] as $file) {
                                // Only process files that were actually uploaded
                                if ($file->getError() === UPLOAD_ERR_OK && $file->getSize() > 0) {
                                    $this->uploadService->store($file, (int)$process_id, null, $indicator_id);
                                }
                            }
                        }
                    }
                }

                $this->Flash->success(__('The Indicators has been saved.'));

                return $this->redirect(['action' => 'index', $process_id]);
            }
            $this->Flash->error(__('The Indicators could not be saved. Please, try again.'));
        }
        $this->set(compact('indicators', 'process', 'qualityDimension', 'relevances'));
        $this->render('form');
    }

    /**
     * Edit method - Edit existing indicators
     *
     * @param string|null $process_id Process id
     * @param string|null $qualityDimension Quality dimension key
     * @return \Cake\Http\Response|null|void Renders form view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized
     */
    public function edit(?string $process_id = null, ?string $qualityDimension = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: []);
        if (!$process || $process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        // Only allow editing during VCIO phase (status 30)
        if ($process->status_id !== 30) {
            throw new ForbiddenException('VCIO phase not active');
        }

        if (!$qualityDimension) {
            $qualityDimension = array_key_first($this->vcioConfig);
        }

        if (!isset($this->vcioConfig[$qualityDimension])) {
            throw new NotFoundException('Invalid quality dimension');
        }

        // Load existing version=0 indicators for this quality dimension
        $quality_dimension_id = $this->vcioConfig[$qualityDimension]['quality_dimension_id'];
        $existingIndicators = $this->Indicators->find()->where([
            'process_id' => $process_id,
            'quality_dimension_id' => $quality_dimension_id,
            'version' => 0,
        ])->all();

        if ($existingIndicators->count() === 0) {
            // No draft indicators found, redirect to add
            return $this->redirect(['action' => 'add', $process_id, $qualityDimension]);
        }

        // Get relevances (same as add)
        $relevantCriterionTypes = $this->Indicators->getCriterionTypeIds($this->vcioConfig, $qualityDimension);
        $criteriaModel = $this->fetchModel('Criteria');
        $relevances = [];
        foreach ($relevantCriterionTypes as $criterionTypeId) {
            $relevances[$criterionTypeId] = current($criteriaModel->calculateRelevanceByCriterionTypeId($process_id, $criterionTypeId));
        }

        // Get all indicators for navigation
        $indicators = $this->Indicators->find('list', keyField: 'quality_dimension_id', valueField: 'id')
            ->where(['process_id' => $process->id])
            ->distinct(['Indicators.quality_dimension_id'])
            ->toArray();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // UPDATE path (use existing indicators)
            $entities = $this->Indicators->patchEntities($existingIndicators, $data['indicators']);

            $result = $this->Indicators->saveMany($entities);

            if ($result) {
                // Handle file attachments (same as add)
                $files = $this->request->getUploadedFiles();
                if (!empty($files['indicators'])) {
                    foreach ($files['indicators'] as $i => $files) {
                        $indicator_id = array_key_exists($i, $result) ? $result[$i]['id'] : null;
                        if (array_key_exists('attachments', $files) && count($files['attachments']) > 0) {
                            foreach ($files['attachments'] as $file) {
                                // Only process files that were actually uploaded
                                if ($file->getError() === UPLOAD_ERR_OK && $file->getSize() > 0) {
                                    $this->uploadService->store($file, (int)$process_id, null, $indicator_id);
                                }
                            }
                        }
                    }
                }

                $this->Flash->success(__('The Indicators has been updated.'));

                return $this->redirect(['action' => 'index', $process_id]);
            }
            $this->Flash->error(__('The Indicators could not be saved. Please, try again.'));
        }

        // Create lookup array for existing indicator data by title
        $existingData = [];
        foreach ($existingIndicators as $indicator) {
            $existingData[$indicator->title] = $indicator;
        }

        $this->set(compact('indicators', 'process', 'qualityDimension', 'relevances', 'existingIndicators', 'existingData'));
        $this->render('form'); // Reuse same form template
    }

    /**
     * Complete method
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to complete.
     */
    public function complete(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
        if ($process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        // Only allow completion during VCIO phase (status 30)
        if ($process->status_id > 30) {
            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        if ($process->status_id < 30) {
            throw new ForbiddenException('VCIO not yet available');
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            // Use transaction to ensure atomicity of version boundary
            $connection = $this->Indicators->getConnection();
            try {
                $connection->transactional(function () use ($process_id, $process) {
                    // Create version 1 from all drafts (THE VERSION BOUNDARY)
                    $updatedCount = $this->Indicators->updateAll(
                        ['version' => 1, 'phase' => 'vcio_complete'],
                        ['process_id' => $process_id, 'version' => 0],
                    );

                    if ($updatedCount === 0) {
                        throw new RuntimeException('No indicators to complete');
                    }

                    $process->status_id = 35; // Go to validation decision
                    if (!$this->Indicators->Processes->save($process)) {
                        $errors = $process->getErrors();
                        throw new RuntimeException('Failed to update process status: ' . json_encode($errors));
                    }
                });

                $this->Flash->success(__('The VCIO Self-assessment has been completed.'));

                return $this->redirect(['action' => 'decideValidation', $process_id]);
            } catch (RuntimeException $e) {
                $this->Flash->error(__('Failed to complete VCIO assessment: {0}', $e->getMessage()));

                return $this->redirect(['action' => 'index', $process_id]);
            }
        }

        // Load indicators for review table
        $indicators = $this->Indicators->find()->where([
            'process_id' => $process_id,
            'version' => 0,
        ])->toArray();

        // Calculate protection levels from criteria
        $criteriaModel = $this->fetchModel('Criteria');
        $criterionTypes = $this->criterionTypes;

        // Calculate relevances from criteria - returns [qd_id => [ct_id => value]]
        $nestedRelevances = $criteriaModel->calculateRelevances($process_id, $criterionTypes);

        // Flatten to [ct_id => value] since each criterion_type belongs to one quality_dimension
        $protectionLevelsByCriterionType = [];
        foreach ($nestedRelevances as $qd_id => $criterionTypesNested) {
            foreach ($criterionTypesNested as $criterionTypeId => $protectionLevel) {
                $protectionLevelsByCriterionType[$criterionTypeId] = $protectionLevel;
            }
        }

        // Use centralized calculation method with weighting support
        $result = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
        );

        $classification = $result['classification'];
        $fulfillment = $result['fulfillment'];
        $protectionLevels = $result['protectionLevels'];

        $currentLanguage = 'de';

        $qualityDimensionsData = $this->Indicators->normalizeForQualityDimensionsTable(
            $this->vcioConfig,
            $indicators,
            $classification,
            $fulfillment,
            $protectionLevels,
            $criterionTypes,
            $process,
        );

        $this->set(compact('process', 'indicators', 'classification', 'fulfillment', 'protectionLevels', 'criterionTypes', 'currentLanguage', 'qualityDimensionsData'));
    }

    /**
     * saveDraft method - Auto-save endpoint for VCIO indicators
     *
     * Handles both candidate (status 30) and examiner (status 40) auto-save:
     * - Candidate: Creates/updates version=0 drafts with level_candidate, evidence
     * - Examiner: Updates existing indicators with level_examiner
     *
     * @param int|null $process_id Process id
     * @return \Cake\Http\Response JSON response
     */
    public function saveDraft(?int $process_id = null)
    {
        $this->request->allowMethod(['post']);

        $identity = $this->request->getAttribute('identity');
        $this->log(sprintf(
            'Auto-save request for process %s from user %s',
            $process_id,
            $identity ? $identity->id : 'unknown',
        ), 'info');

        try {
            $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
        } catch (RecordNotFoundException $e) {
            $this->log(sprintf('Auto-save failed: Process %s not found', $process_id), 'warning');

            return $this->response->withStatus(404)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Process not found',
                ]));
        }

        $isCandidate = $process->candidate_user === $identity->id;
        $isExaminer = $process->isUserExaminer($identity->id);

        if ($isCandidate && $process->status_id === 30) {
            return $this->saveDraftCandidate($process, $identity);
        }

        if ($isExaminer && $process->status_id === 40) {
            return $this->saveDraftExaminer($process, $identity);
        }

        $this->log(sprintf(
            'Auto-save unauthorized: User %s (candidate=%s, examiner=%s) for process %s status %s',
            $identity->id,
            $isCandidate ? 'yes' : 'no',
            $isExaminer ? 'yes' : 'no',
            $process_id,
            $process->status_id,
        ), 'warning');

        return $this->response->withStatus(403)
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => false,
                'error' => 'Not authorized or invalid process state',
            ]));
    }

    /**
     * Save draft for candidate during VCIO self-assessment (status 30)
     *
     * @param \App\Model\Entity\Process $process Process entity
     * @param \Authentication\Identity $identity Current user identity
     * @return \Cake\Http\Response JSON response
     */
    private function saveDraftCandidate(Process $process, Identity $identity): Response
    {
        $incomingIndicators = $this->request->getData('indicators', []);
        $savedCount = 0;
        $allowedFields = ['level_candidate', 'quality_dimension_id', 'evidence', 'title'];

        foreach ($incomingIndicators as $title => $data) {
            if (empty($title)) {
                continue;
            }

            $safeData = array_intersect_key($data, array_flip($allowedFields));

            if (isset($safeData['level_candidate'])) {
                $level = $safeData['level_candidate'];
                if (!is_numeric($level) || $level < 0 || $level > 3) {
                    return $this->response->withStatus(422)
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'error' => 'Invalid level_candidate value (must be 0-3)',
                            'indicator' => $title,
                        ]));
                }
                $safeData['level_candidate'] = (int)$level;
            }

            if (isset($safeData['evidence']) && strlen($safeData['evidence']) > 10000) {
                return $this->response->withStatus(422)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Evidence text too long (max 10000 characters)',
                        'indicator' => $title,
                    ]));
            }

            $indicator = $this->Indicators->find()->where([
                'process_id' => $process->id,
                'title' => $title,
                'version' => 0,
            ])->first();

            if ($indicator) {
                $indicator = $this->Indicators->patchEntity($indicator, $safeData);
            } else {
                $safeData['process_id'] = $process->id;
                $safeData['title'] = $title;
                $safeData['version'] = 0;
                $safeData['phase'] = 'vcio';
                $indicator = $this->Indicators->newEntity($safeData);
            }

            if (!$this->Indicators->save($indicator)) {
                return $this->response->withStatus(422)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Validation failed',
                        'errors' => $indicator->getErrors(),
                        'indicator' => $title,
                    ]));
            }
            $savedCount++;
        }

        $this->log(sprintf(
            'Auto-save (candidate) completed: %d indicators saved for process %s by user %s',
            $savedCount,
            $process->id,
            $identity->id,
        ), 'info');

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'saved' => $savedCount,
                'timestamp' => date('H:i:s'),
            ]));
    }

    /**
     * Save draft for examiner during validation (status 40)
     *
     * @param \App\Model\Entity\Process $process Process entity
     * @param \Authentication\Identity $identity Current user identity
     * @return \Cake\Http\Response JSON response
     */
    private function saveDraftExaminer(Process $process, Identity $identity): Response
    {
        $incomingIndicators = $this->request->getData('indicators', []);
        $savedCount = 0;

        foreach ($incomingIndicators as $indicatorId => $data) {
            if (empty($indicatorId) || !is_numeric($indicatorId)) {
                continue;
            }

            $indicator = $this->Indicators->find()->where([
                'Indicators.id' => (int)$indicatorId,
                'Indicators.process_id' => $process->id,
            ])->first();

            if (!$indicator) {
                $this->log(sprintf(
                    'Auto-save (examiner): Indicator %s not found for process %s',
                    $indicatorId,
                    $process->id,
                ), 'warning');
                continue;
            }

            if (isset($data['level_examiner'])) {
                $level = $data['level_examiner'];
                if (!is_numeric($level) || $level < 0 || $level > 3) {
                    return $this->response->withStatus(422)
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'error' => 'Invalid level_examiner value (must be 0-3)',
                            'indicator' => $indicatorId,
                        ]));
                }
                $indicator->level_examiner = (int)$level;
            }

            if (!$this->Indicators->save($indicator)) {
                return $this->response->withStatus(422)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Validation failed',
                        'errors' => $indicator->getErrors(),
                        'indicator' => $indicatorId,
                    ]));
            }
            $savedCount++;
        }

        $this->log(sprintf(
            'Auto-save (examiner) completed: %d indicators saved for process %s by user %s',
            $savedCount,
            $process->id,
            $identity->id,
        ), 'info');

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'saved' => $savedCount,
                'timestamp' => date('H:i:s'),
            ]));
    }

    /**
     * Decide Validation method
     *
     * Shows the validation decision screen at status 35.
     * Displays different options based on risk level and examiner status.
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized.
     */
    public function decideValidation(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners', 'Projects']);

        if (
            $process->candidate_user !== $this->request->getAttribute('identity')->id
            || $process->status_id !== 35
        ) {
            throw new ForbiddenException();
        }

        $criteriaModel = $this->fetchModel('Criteria');
        $riskLevel = $criteriaModel->calculateOverallRiskLevel($process->id);

        $hasExaminer = !empty($process->examiners);
        $qualificationConfirmed = false;

        if ($hasExaminer && $riskLevel === 'high') {
            // Check if qualification confirmed in junction table
            $examinersProcessesTable = $this->fetchTable('ProcessesExaminers');
            $junction = $examinersProcessesTable->find()
                ->where([
                    'process_id' => $process_id,
                    'user_id' => $process->examiners[0]->id,
                ])
                ->first();
            $qualificationConfirmed = $junction ? (bool)$junction->qualification_confirmed : false;
        }

        $this->set(compact('process', 'riskLevel', 'hasExaminer', 'qualificationConfirmed'));
    }

    /**
     * Confirm Qualification method
     *
     * Handles two scenarios:
     * 1. High risk + no examiner: Invites examiner and confirms qualification
     * 2. High risk + examiner assigned: Confirms qualification only
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response Redirects appropriately
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized.
     */
    public function confirmQualification(?string $process_id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners', 'Projects']);

        if ($process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        $additionalParticipants = $this->request->getData('additional_participants');

        // Scenario 1: High risk + no examiner (inviting examiner)
        if (!empty($additionalParticipants)) {
            $usersTable = $this->fetchTable('Users');
            $processesExaminersTable = $this->fetchTable('ProcessesExaminers');

            foreach ($additionalParticipants as $participant) {
                if (empty($participant['email']) || empty($participant['name'])) {
                    continue;
                }

                try {
                    $userId = $usersTable->getCandidateExaminerUserId(
                        $participant['email'],
                        $participant['name'],
                        __('You have been invited as an examiner'),
                    );
                    $user = $usersTable->get($userId);
                } catch (\Exception $e) {
                    $this->Flash->error(__('Could not create user. Please try again.'));
                    continue;
                }

                $junction = $processesExaminersTable->newEntity([
                    'process_id' => $process_id,
                    'user_id' => $user->id,
                    'qualification_confirmed' => true,
                ]);

                if (!$processesExaminersTable->save($junction)) {
                    $this->Flash->error(__('Could not add examiner. Please try again.'));
                    continue;
                }
            }

            $process->status_id = 40;
            if (!$this->Indicators->Processes->save($process)) {
                $this->Flash->error(__('Could not update process status. Please try again.'));

                return $this->redirect(['action' => 'decideValidation', $process_id]);
            }

            $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
            $notificationModel = $this->fetchModel('Notifications');
            $desc = __('The VCIO Self-assessment for process "{0}" is ready for validation.', $process->title);

            foreach ($process->examiners as $examiner) {
                $notificationModel->createNotification(
                    __('Notification: VCIO Validation'),
                    $desc,
                    $examiner->id,
                    $process->id,
                );
            }

            $this->Flash->success(__('Examiner invited and qualification confirmed. Process advanced to validation.'));

            return $this->redirect(['controller' => 'Processes', 'action' => 'view', $process_id]);
        }

        // Scenario 2: High risk + examiner assigned (confirmation only)
        if (empty($process->examiners)) {
            throw new ForbiddenException(__('No examiner assigned. Please add an examiner first.'));
        }

        $processesExaminersTable = $this->fetchTable('ProcessesExaminers');
        $junction = $processesExaminersTable->find()
            ->where([
                'process_id' => $process_id,
                'user_id' => $process->examiners[0]->id,
            ])
            ->first();

        if ($junction) {
            $junction->qualification_confirmed = true;
            if (!$processesExaminersTable->save($junction)) {
                $this->Flash->error(__('Could not confirm qualification. Please try again.'));

                return $this->redirect(['action' => 'decideValidation', $process_id]);
            }
        } else {
            $this->Flash->error(__('Examiner relationship not found. Please contact support.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        $process->status_id = 40;
        if (!$this->Indicators->Processes->save($process)) {
            $this->Flash->error(__('Could not update process status. Please try again.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        $notificationModel = $this->fetchModel('Notifications');
        $desc = __('The VCIO Self-assessment for process "{0}" is ready for validation.', $process->title);

        foreach ($process->examiners as $examiner) {
            $notificationModel->createNotification(
                __('Notification: VCIO Validation'),
                $desc,
                $examiner->id,
                $process->id,
            );
        }

        $this->Flash->success(__('Examiner qualification confirmed. Process advanced to validation.'));

        return $this->redirect(['action' => 'decideValidation', $process_id]);
    }

    /**
     * Skip Validation method
     *
     * Allows candidate to skip validation for moderate/low risk processes.
     * Advances process directly to status 60 (complete).
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response Redirects to dashboard
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized or high risk.
     */
    public function skipValidation(?string $process_id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        $process = $this->Indicators->Processes->get($process_id);

        if (
            $process->candidate_user !== $this->request->getAttribute('identity')->id
            || $process->status_id !== 35
        ) {
            throw new ForbiddenException();
        }

        $criteriaModel = $this->fetchModel('Criteria');
        $riskLevel = $criteriaModel->calculateOverallRiskLevel($process->id);

        if ($riskLevel === null) {
            $this->Flash->error(__('Unable to determine risk level. Please complete all required criteria.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        if ($riskLevel === 'high') {
            throw new ForbiddenException(__('High risk processes require validation.'));
        }

        $process->status_id = 60;
        if (!$this->Indicators->Processes->save($process)) {
            $this->Flash->error(__('Could not update process status. Please try again.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        $this->Flash->success(__('Validation skipped. Process marked as complete.'));

        return $this->redirect('/');
    }

    /**
     * Proceed With Validation method
     *
     * Advances process to status 40 for examiner validation.
     * Verifies examiner exists and (for high risk) qualification is confirmed.
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response Redirects to dashboard or back to decision screen
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized.
     */
    public function proceedWithValidation(?string $process_id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);

        if (
            $process->candidate_user !== $this->request->getAttribute('identity')->id
            || $process->status_id !== 35
        ) {
            throw new ForbiddenException();
        }

        // Verify examiner exists
        if (empty($process->examiners)) {
            $this->Flash->error(__('Please add an examiner before proceeding to validation.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        // For high risk, verify qualification confirmed
        $criteriaModel = $this->fetchModel('Criteria');
        $riskLevel = $criteriaModel->calculateOverallRiskLevel($process->id);

        if ($riskLevel === null) {
            $this->Flash->error(__('Unable to determine risk level. Please complete all required criteria.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        if ($riskLevel === 'high') {
            $processesExaminersTable = $this->fetchTable('ProcessesExaminers');
            $junction = $processesExaminersTable->find()
                ->where([
                    'process_id' => $process_id,
                    'user_id' => $process->examiners[0]->id,
                ])
                ->first();

            if (!$junction || !$junction->qualification_confirmed) {
                $this->Flash->error(__('Please confirm examiner qualification before proceeding.'));

                return $this->redirect(['action' => 'decideValidation', $process_id]);
            }
        }

        $process->status_id = 40;
        if (!$this->Indicators->Processes->save($process)) {
            $this->Flash->error(__('Could not update process status. Please try again.'));

            return $this->redirect(['action' => 'decideValidation', $process_id]);
        }

        // Notify examiner(s)
        $notificationModel = $this->fetchModel('Notifications');
        $desc = __('The VCIO Self-assessment for process "{0}" is ready for validation.', $process->title);

        foreach ($process->examiners as $examiner) {
            $notificationModel->createNotification(
                __('Notification: VCIO Validation'),
                $desc,
                $examiner->id,
                $process->id,
            );
        }

        $this->Flash->success(__('Process advanced to validation. Examiner has been notified.'));

        return $this->redirect('/');
    }

    /**
     * Validation method
     *
     * Shows the Validation page for the given process_id.
     * If the user is not authorized to complete, a ForbiddenException is thrown.
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to complete.
     */
    public function validation(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
        if (!$process->isUserExaminer($this->request->getAttribute('identity')->id) || !$process) {
            throw new ForbiddenException();
        }

        // Check if for this quality_dimension_id $indicators exists
        $indicators = $this->Indicators->find('list', keyField: 'quality_dimension_id', valueField: 'id')
            ->where(['process_id' => $process->id, 'level_examiner IS NOT NULL'])
            ->distinct(['Indicators.quality_dimension_id'])
            ->toArray();

        $this->set(compact('process', 'indicators'));
    }

    /**
     * Validate method
     *
     * Shows the Validation page for the given process_id.
     * If the user is not authorized to validate, a ForbiddenException is thrown.
     *
     * @param string|null $process_id Process id.
     * @param string|null $qualityDimension Quality dimension.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to validate.
     */
    public function validate(?string $process_id = null, ?string $qualityDimension = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
        if (!$process->isUserExaminer($this->request->getAttribute('identity')->id) || !$process || $process->status_id != 40) { // $this->statuses
            throw new ForbiddenException();
        }
        $indicatorsList = $this->Indicators->find('list', keyField: 'quality_dimension_id', valueField: 'id')
            ->where(['process_id' => $process->id, 'level_examiner IS NOT NULL'])
            ->distinct(['Indicators.quality_dimension_id'])
            ->toArray();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $indicators = $this->Indicators->find()->where(['id IN' => array_keys($data['indicators'])])->all();
            $entities = $this->Indicators->patchEntities($indicators, $data['indicators'], ['validate' => false]);
            if ($this->Indicators->saveMany($entities)) {
                $this->Flash->success(__('The Validation has been saved.'));

                return $this->redirect(['action' => 'validation', $process_id]);
            }
            $this->Flash->error(__('The Validation could not be saved. Please, try again.'));
        }

        $query = $this->Indicators->Processes->Comments->find('list', keyField: 'id', valueField: 'reference_id', conditions: ['process_id' => $process->id]);
        $commentReferences = $query->toArray();

        $indicators = $this->Indicators->find('all', contain: ['Uploads'])
            ->where(['process_id' => $process->id, 'quality_dimension_id' => $this->vcioConfig[$qualityDimension]['quality_dimension_id']])
            ->orderBy('Indicators.quality_dimension_id')
            ->toArray();

        $this->set(compact('indicatorsList', 'indicators', 'process', 'qualityDimension', 'commentReferences'));
    }

    /**
     * Complete Validation method
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to complete.
     */
    public function completeValidation(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
        if (!$process->isUserExaminer($this->request->getAttribute('identity')->id) || !$process) {
            throw new ForbiddenException();
        }

        $indicators = $this->Indicators->find()->where([
            'process_id' => $process_id,
            'version' => 1,
            'level_examiner IS NOT' => null,
        ])->toArray();

        $criteriaModel = $this->fetchModel('Criteria');
        $nestedRelevances = $criteriaModel->calculateRelevances($process_id, $this->criterionTypes);

        $protectionLevelsByCriterionType = [];
        foreach ($nestedRelevances as $qd_id => $criterionTypesNested) {
            foreach ($criterionTypesNested as $criterionTypeId => $protectionLevel) {
                $protectionLevelsByCriterionType[$criterionTypeId] = $protectionLevel;
            }
        }

        $resultCandidate = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
            'level_candidate',
        );
        $classificationCandidate = $resultCandidate['classification'];

        $resultExaminer = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
            'level_examiner',
        );
        $classification = $resultExaminer['classification'];
        $fulfillment = $resultExaminer['fulfillment'];
        $protectionLevels = $resultExaminer['protectionLevels'];

        $qualityDimensionsData = $this->Indicators->normalizeForQualityDimensionsTable(
            $this->vcioConfig,
            $indicators,
            $classification,
            $fulfillment,
            $protectionLevels,
            $this->criterionTypes,
            $process,
            $classificationCandidate,
            'validate',
        );

        if ($this->request->is(['patch', 'post', 'put'])) {
            $connection = $this->Indicators->getConnection();
            try {
                $connection->transactional(function () use ($process_id, $process) {
                    $updatedCount = $this->Indicators->updateAll(
                        ['version' => 2, 'phase' => 'validation_complete'],
                        ['process_id' => $process_id, 'version' => 1],
                    );

                    if ($updatedCount === 0) {
                        throw new RuntimeException('No indicators to finalize');
                    }

                    $process->status_id = 50;
                    if (!$this->Indicators->Processes->save($process)) {
                        $errors = $process->getErrors();
                        throw new RuntimeException('Failed to update process status: ' . json_encode($errors));
                    }
                });

                $notificationModel = $this->fetchModel('Notifications');
                $desc = __('The VCIO Validation for the process "{0}" has been completed by the examiner.', $process->title);
                $notificationModel->createNotification(__('Notification: VCIO Validation completed'), $desc, $process->candidate_user, $process->id);

                $this->Flash->success(__('The VCIO Validation has been completed and the candidate will be notified.'));

                return $this->redirect('/');
            } catch (RuntimeException $e) {
                $this->Flash->error(__('Failed to complete validation: {0}', $e->getMessage()));

                return $this->redirect(['action' => 'validation', $process_id]);
            }
        }
        $this->set(compact('process', 'qualityDimensionsData'));
    }

    /**
     * Accept Validation method
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized to complete.
     */
    public function acceptValidation(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Examiners']);
        if (!$process || $process->candidate_user !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }

        $indicators = $this->Indicators->find()->where([
            'process_id' => $process_id,
            'version' => 2,
            'level_examiner IS NOT' => null,
        ])->toArray();

        $criteriaModel = $this->fetchModel('Criteria');
        $nestedRelevances = $criteriaModel->calculateRelevances($process_id, $this->criterionTypes);

        $protectionLevelsByCriterionType = [];
        foreach ($nestedRelevances as $qd_id => $criterionTypesNested) {
            foreach ($criterionTypesNested as $criterionTypeId => $protectionLevel) {
                $protectionLevelsByCriterionType[$criterionTypeId] = $protectionLevel;
            }
        }

        $resultCandidate = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
            'level_candidate',
        );
        $classificationCandidate = $resultCandidate['classification'];

        $resultExaminer = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
            'level_examiner',
        );
        $classification = $resultExaminer['classification'];
        $fulfillment = $resultExaminer['fulfillment'];
        $protectionLevels = $resultExaminer['protectionLevels'];

        $qualityDimensionsData = $this->Indicators->normalizeForQualityDimensionsTable(
            $this->vcioConfig,
            $indicators,
            $classification,
            $fulfillment,
            $protectionLevels,
            $this->criterionTypes,
            $process,
            $classificationCandidate,
        );

        if ($this->request->is(['patch', 'post', 'put'])) {
            $process->status_id = 60; // $this->statuses
            $this->Indicators->Processes->save($process);

            // Create a notification
            $notificationModel = $this->fetchModel('Notifications');
            $desc = __('The VCIO Validation for the process "{0}" has been accepted by the candidate.', $process->title);

            // Notify all examiners
            if (!empty($process->examiners)) {
                foreach ($process->examiners as $examiner) {
                    $notificationModel->createNotification(
                        __('Notification: VCIO Validation accepted'),
                        $desc,
                        $examiner->id,
                        $process->id,
                    );
                }
            }

            $this->Flash->success(__('The VCIO Validation has been accepted.'));
            return $this->redirect(['controller' => 'Processes', 'action' => 'totalResult', $process->id]);
        }
        $this->set(compact('process', 'qualityDimensionsData'));
    }

    /**
     * View method
     *
     * Displays VCIO indicator results for a given process.
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized.
     */
    public function view(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Projects', 'Examiners']);

        if ($process->status_id < 30) {
            throw new ForbiddenException();
        }

        // Fetch all indicators for this process grouped by quality dimension
        $indicators = $this->Indicators->find('all')
            ->where(['process_id' => $process_id])
            ->orderBy(['quality_dimension_id' => 'ASC'])
            ->toArray();

        if ($process->status_id == 30 && empty($indicators)) {
            throw new ForbiddenException();
        }

        // Calculate protection levels from criteria
        $criteriaModel = $this->fetchModel('Criteria');

        // Calculate relevances from criteria - returns [qd_id => [ct_id => value]]
        $nestedRelevances = $criteriaModel->calculateRelevances($process_id, $this->criterionTypes);

        // Flatten to [ct_id => value] since each criterion_type belongs to one quality_dimension
        $protectionLevelsByCriterionType = [];
        foreach ($nestedRelevances as $qd_id => $criterionTypes) {
            foreach ($criterionTypes as $criterionTypeId => $protectionLevel) {
                $protectionLevelsByCriterionType[$criterionTypeId] = $protectionLevel;
            }
        }

        // Use centralized calculation method with weighting support
        $result = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
        );

        $classification = $result['classification'];
        $fulfillment = $result['fulfillment'];
        $protectionLevels = $result['protectionLevels'];

        // Debug logging
        $this->log('=== VCIO CLASSIFICATION DEBUG FOR PROCESS ' . $process_id . ' ===', 'debug');
        $this->log('protectionLevelsByCriterionType: ' . json_encode($protectionLevelsByCriterionType), 'debug');
        $this->log('classification: ' . json_encode($classification), 'debug');
        $this->log('fulfillment: ' . json_encode($fulfillment), 'debug');

        $qualityDimensionsData = $this->Indicators->normalizeForQualityDimensionsTable(
            $this->vcioConfig,
            $indicators,
            $classification,
            $fulfillment,
            $protectionLevels,
            $this->criterionTypes,
            $process,
        );

        $this->set(compact('process', 'indicators', 'classification', 'fulfillment', 'protectionLevels', 'qualityDimensionsData'));
    }

    /**
     * Displays validated VCIO results with dual classification (candidate + examiner).
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Http\Exception\ForbiddenException When validation is not complete.
     */
    public function validationView(?string $process_id = null)
    {
        $process = $this->Indicators->Processes->get($process_id, contain: ['Projects', 'Examiners']);

        if ($process->status_id < 50) {
            throw new ForbiddenException('Validation not yet complete');
        }

        $indicators = $this->Indicators->find()->where([
            'process_id' => $process_id,
            'version' => 2,
            'level_examiner IS NOT' => null,
        ])->toArray();

        $criteriaModel = $this->fetchModel('Criteria');
        $nestedRelevances = $criteriaModel->calculateRelevances($process_id, $this->criterionTypes);

        $protectionLevelsByCriterionType = [];
        foreach ($nestedRelevances as $qd_id => $criterionTypesNested) {
            foreach ($criterionTypesNested as $criterionTypeId => $protectionLevel) {
                $protectionLevelsByCriterionType[$criterionTypeId] = $protectionLevel;
            }
        }

        $resultCandidate = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
            'level_candidate',
        );
        $classificationCandidate = $resultCandidate['classification'];

        $resultExaminer = $this->Indicators->calculateVcioClassification(
            $indicators,
            $this->vcioConfig,
            $protectionLevelsByCriterionType,
            'level_examiner',
        );
        $classification = $resultExaminer['classification'];
        $fulfillment = $resultExaminer['fulfillment'];
        $protectionLevels = $resultExaminer['protectionLevels'];

        $qualityDimensionsData = $this->Indicators->normalizeForQualityDimensionsTable(
            $this->vcioConfig,
            $indicators,
            $classification,
            $fulfillment,
            $protectionLevels,
            $this->criterionTypes,
            $process,
            $classificationCandidate,
        );

        $this->set(compact('process', 'qualityDimensionsData'));
    }

    /**
     * Get short titles from the JSON configuration
     *
     * @param array $config The JSON configuration.
     * @return array The short titles with keys.
     */
    private function getShortTitles(array $config): array
    {
        // Extract all short_titles
        //$shortTitles = array_column(v, 'short_title');

        return array_map(function ($item) {
            return $item['short_title'] ?? $item['title']; // Default is title when short_title is not set
        }, $config);
    }
}
