<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\ForbiddenException;

/**
 * Criteria Controller
 *
 * @property \App\Model\Table\CriteriaTable $Criteria
 */
class CriteriaController extends AppController
{
    use ModelAwareTrait;

    private array $protectionNeedsAnalysis;

    private array $qualityDimensions;

    public function initialize(): void
    {
        parent::initialize();

        // Filter out questions that receive values from other criteria (should not be displayed to users)
        $this->protectionNeedsAnalysis = $this->Criteria->getProtectionNeedsAnalysisConfig();

        // If a receivesValueFromCriterion: <criterion-id> is set, the rating calculation for that question must be equated with the protection requirement of the referenced criterion
        $this->set(['protectionNeedsAnalysis' => $this->filterReceivesValueFromCriterion($this->protectionNeedsAnalysis)]);

        $this->qualityDimensions = $this->Criteria->getQualityDimensionIds($this->protectionNeedsAnalysis);
        ksort($this->qualityDimensions);
        $this->set(['qualityDimensionIds' => $this->qualityDimensions]);
    }

    /**
     * index method
     *
     * @param $process_id
     * @return void
     */
    public function index($process_id = null)
    {
        $process = $this->Criteria->Processes->get($process_id, contain: ['Examiners']);
        if ($process->candidate_user !== $this->request->getAttribute('identity')->id || $process->status_id !== 20) {
            throw new ForbiddenException();
        }

        $stateData = $this->getNavigationStateForProcess((int)$process_id);

        $this->set(compact('process'));
        $this->set($stateData);
    }

    /**
     * editRateQD method
     *
     * @param string|null $process_id Process id.
     * @param string|null $quality_dimension.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function editRateQD(?string $process_id = null, ?string $quality_dimension = null, $question_id = null)
    {
        $this->rateQD($process_id, $quality_dimension);
        $this->set('is_edit', true);
        // VE has no AP questions
        if ((int)$question_id === 0 && $quality_dimension === 'VE') {
            $question_id = 1;
        }

        $this->set('question_id', $question_id);
        $this->render('rate_q_d');
    }

    /**
     * rateQD method
     *
     * @param string|null $process_id Process id.
     * @param string|null $quality_dimension.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function rateQD(?string $process_id = null, ?string $quality_dimension = null)
    {
        $criterion = $this->Criteria->newEmptyEntity();
        $process = $this->Criteria->Processes->get($process_id, contain: ['Examiners']);
        if ($process->candidate_user !== $this->request->getAttribute('identity')->id || !array_key_exists($quality_dimension, $this->protectionNeedsAnalysis) || $process->status_id !== 20) { // $this->statuses
            throw new ForbiddenException();
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $transformedCriteria = $this->transformRatingsArray($data);
            $transformedCriteria = $this->handleRelatedQuestions($transformedCriteria);

            $redirect = ['action' => 'index', $process_id];

            // Extract quality dimension ID from first criterion
            $quality_dimension_id = $this->protectionNeedsAnalysis[$quality_dimension]['quality_dimension_id'];
            $current_question_id = $data['question_id'] ?? 0;

            // Extract submitted titles for filtering query
            $submittedTitles = array_keys($transformedCriteria);

            // Check if version=0 criteria exist (from auto-save or previous edit)
            // CRITICAL: Filter by submitted titles to prevent loading orphaned criteria
            $existingCriteria = $this->Criteria->find()->where([
                'process_id' => $process_id,
                'quality_dimension_id' => $quality_dimension_id,
                'question_id' => $current_question_id,
                'version' => 0,
                'title IN' => $submittedTitles,
            ])->all();

            if ($existingCriteria->count() > 0) {
                // UPDATE path (auto-save already created them, or editing existing)
                $this->log("UPDATE path: Matching {$existingCriteria->count()} entities by title", 'debug');

                // BUILD lookup map: title => entity
                // This prevents data corruption when multiple criteria exist per question
                // (e.g., DA-Z12 and DA-Z13 both in question_id=0)
                $entityMap = [];
                foreach ($existingCriteria as $entity) {
                    $entityMap[$entity->title] = $entity;
                }

                // MATCH data to entities by title (NOT by index position)
                $entities = [];
                foreach ($transformedCriteria as $title => $data) {
                    if (isset($entityMap[$title])) {
                        // UPDATE existing entity
                        $entity = $this->Criteria->patchEntity($entityMap[$title], $data);
                        $entities[] = $entity;
                        $this->log("UPDATE: {$title} with value={$data['value']}", 'debug');
                    } else {
                        // INSERT new entity (schema evolved, new criterion added)
                        $entity = $this->Criteria->newEntity($data);
                        $entities[] = $entity;
                        $this->log("INSERT (new criterion): {$title} with value={$data['value']}", 'debug');
                    }
                }

                // Continue to next question type if not on last question
                if ($current_question_id < 2) {
                    $next_question_id = $current_question_id + 1;
                    $redirect = ['action' => 'editRateQD', 'process_id' => $process_id, 'qd_id' => $quality_dimension, 'question_id' => $next_question_id];
                }
            } else {
                // INSERT path (fallback if auto-save didn't run - JS disabled, offline, etc.)
                $this->log('INSERT path: Creating ' . count($transformedCriteria) . ' new entities', 'debug');
                $entities = $this->Criteria->newEntities($transformedCriteria);

                // Continue to next question type if not on last question
                if ($current_question_id < 2) {
                    $redirect = ['action' => 'rateQD', 'process_id' => $process_id, 'qd_id' => $quality_dimension];
                }
            }

            $result = $this->Criteria->saveMany($entities);
            if ($result) {
                $this->Flash->success(__('The Criteria has been saved.'));
                $this->redirect($redirect);
            } else {
                // Validation failed - collect all errors
                $errors = [];
                foreach ($entities as $entity) {
                    if ($entity->hasErrors()) {
                        $entityErrors = $entity->getErrors();
                        foreach ($entityErrors as $field => $fieldErrors) {
                            foreach ($fieldErrors as $error) {
                                $errors[] = $error;
                            }
                        }
                    }
                }

                if (!empty($errors)) {
                    $this->Flash->error(__('Please correct the errors below:') . ' ' . implode(', ', $errors));
                } else {
                    $this->Flash->error(__('Could not save the criteria. Please try again.'));
                }
                // Form will re-render with errors
            }
        }

        $quality_dimension_id = $this->protectionNeedsAnalysis[$quality_dimension]['quality_dimension_id'];

        // Fetch criteria for current quality dimension (for form population)
        $currentQdCriteria = $this->Criteria->find('all')
            ->select(['id', 'title', 'value', 'quality_dimension_id', 'question_id'])
            ->where(['process_id' => $process_id, 'quality_dimension_id' => $quality_dimension_id])
            ->orderByDesc('question_id')
            ->toArray();

        // Get navigation state for sidebar
        $stateData = $this->getNavigationStateForProcess((int)$process_id);

        $question_id = 0;
        if (count($currentQdCriteria)) {
            // if questions types already answered increment question_id
            $question_id = $currentQdCriteria[array_key_first($currentQdCriteria)]['question_id'] + 1;
        } elseif ($quality_dimension === 'VE' && count($currentQdCriteria) === 0) {
            $question_id = 1;
        }

        // Pass both current QD criteria and all criteria to view
        $this->set('criteria', $currentQdCriteria);  // For form population
        $this->set(compact('criterion', 'process', 'quality_dimension', 'question_id'));
        $this->set($stateData);
    }

    /**
     * complete method
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function complete(?string $process_id = null)
    {
        $process = $this->Criteria->Processes->get($process_id, contain: ['Examiners', 'Projects']);
        if ($process->candidate_user !== $this->request->getAttribute('identity')->id || $process->status_id !== 20) { // $this->statuses
            throw new ForbiddenException();
        }

        // Prepare criteria data for review table
        $relevances = $this->Criteria->calculateRelevances($process_id, $this->criterionTypes);

        if ($this->request->is(['patch', 'post', 'put'])) {
            // Create version 1 from all drafts (THE VERSION BOUNDARY)
            $this->Criteria->updateAll(
                ['version' => 1, 'phase' => 'pna_complete'],
                ['process_id' => $process_id, 'version' => 0],
            );

            $process->status_id = 30; // $this->statuses

            // Add missing criteria from "receivesValueFromCriterion"
            $criterionWithCriterionKeyRelation = $this->Criteria->findObjectsWithCriterionKey($this->protectionNeedsAnalysis);
            foreach ($criterionWithCriterionKeyRelation as $criterion) {
                $criterionEntity = $this->Criteria->newEmptyEntity();
                $data = [
                    'title' => $criterion['id'],
                    'question_id' => 1,
                    'quality_dimension_id' => $this->protectionNeedsAnalysis[$criterion['dimension']]['quality_dimension_id'],
                    'value' => current($this->Criteria->calculateRelevanceByCriterionTypeId($process_id, $criterion['receivesValueFromCriterion'])),
                    'protection_target_category_id' => $criterion['category'],
                    'criterion_type_id' => $criterion['criteria'],
                    'process_id' => $process_id,
                    'version' => 1,
                    'phase' => 'pna_complete',
                ];
                $criterionEntity = $this->Criteria->patchEntity($criterionEntity, $data);
                $this->Criteria->save($criterionEntity);
            }

            $this->Criteria->Processes->save($process);

            // Notify project participants (project owner + examiners)
            $this->notifyProjectParticipants(
                $process,
                __('Notification: PNA Completed'),
                __('The Protection Needs Analysis for process "{0}" has been completed by the candidate.', $process->title),
            );

            $this->Flash->success(__('The Protection Needs Analysis has been completed.'));

            return $this->redirect(['controller' => 'Processes', 'action' => 'view', $process_id]);
        }
        $qualityDimensionsData = $this->Criteria->normalizeForQualityDimensionsTable(
            $this->protectionNeedsAnalysis,
            $relevances,
            $this->criterionTypes,
            $process,
        );
        $this->set(compact('process', 'relevances', 'qualityDimensionsData'));
    }

    /**
     * saveDraft method - Auto-save endpoint for PNA forms
     *
     * @param int|null $process_id Process id
     * @return \Cake\Http\Response JSON response
     */
    public function saveDraft(?int $process_id = null)
    {
        $this->request->allowMethod(['post']);

        $process = $this->Criteria->Processes->get($process_id);

        // Authorization: only candidate can save
        $identity = $this->request->getAttribute('identity');
        if ($process->candidate_user !== $identity->id) {
            return $this->response->withStatus(403)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Not authorized',
                ]));
        }

        // Only allow during PNA phase (status 20)
        if ($process->status_id !== 20) {
            return $this->response->withStatus(400)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'PNA not active',
                ]));
        }

        $incomingCriteria = $this->request->getData('criteria', []);
        $savedCount = 0;

        foreach ($incomingCriteria as $title => $data) {
            // Find or create version=0 criterion (UPSERT logic)
            $criterion = $this->Criteria->find()->where([
                'process_id' => $process_id,
                'title' => $title,
                'version' => 0,
            ])->first();

            if ($criterion) {
                // UPDATE existing draft
                $criterion = $this->Criteria->patchEntity($criterion, $data);
            } else {
                // INSERT new draft
                $data['process_id'] = $process_id;
                $data['title'] = $title;
                $data['version'] = 0;
                $data['phase'] = 'pna';
                $criterion = $this->Criteria->newEntity($data);
            }

            if ($this->Criteria->save($criterion)) {
                $savedCount++;
            }
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'saved' => $savedCount,
                'timestamp' => date('H:i:s'),
            ]));
    }

    /**
     * XHR checkForRelatedQuestion method
     *
     * @return void
     */
    public function checkForRelatedQuestion()
    {
        if (!$this->request->is('post')) {
            throw new ForbiddenException();
        }
        $data = $this->request->getData(); // [question_id] => DA-Z18, [value] => 2, [process_id] => 11
        if ($this->Criteria->find('all')->where(['title' => $data['question_id'], 'process_id' => $data['process_id']])->count() > 0) {
            $relatedQuestion = $this->Criteria->find('all')->where(['title' => $data['question_id'], 'process_id' => $data['process_id']])->first();
            $data = json_encode(['success' => true, 'disable' => true, 'value' => $relatedQuestion->value]);
        } else {
            $data = json_encode(['success' => true, 'disable' => false]);
        }
        $this->viewBuilder()->setClassName('Ajax');
        $this->viewBuilder()->setTemplate('/element/ajax');
        $this->set(compact('data'));
    }

    /**
     * View method
     *
     * @param string|null $process_id Process id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $process_id = null)
    {
        $process = $this->Criteria->Processes->get($process_id, contain: ['Projects', 'Examiners']);
        $userId = $this->request->getAttribute('identity')->id;
        if (($userId !== $process->candidate_user && !$process->isUserExaminer($userId)) || $process->status_id < 30) { // $this->statuses
            throw new ForbiddenException();
        }
        $relevances = $this->Criteria->calculateRelevances($process_id, $this->criterionTypes);
        $qualityDimensionsData = $this->Criteria->normalizeForQualityDimensionsTable(
            $this->protectionNeedsAnalysis,
            $relevances,
            $this->criterionTypes,
            $process,
        );
        $this->set(compact('relevances', 'process', 'qualityDimensionsData'));
    }

    /**
     * Converting
     * Array
     * (
     * [quality_dimension_id] => 10
     * [question_id] => 0
     * [process_id] => 6
     * [criteria] => Array
     * (
     * [DA-Z12] => Array
     * (
     * [protection_target_category_id] => 0
     * [criterion_type_id] => 11
     * [title] => DA-Z12
     * [value] => 1
     * )
     * ...
     * to this:
     * Array
     * (
     * [DA-Z12] => Array
     * (
     * [protection_target_category_id] => 0
     * [criterion_type_id] => 11
     * [title] => DA-Z12
     * [value] => 1
     * [quality_dimension_id] => 10
     * [question_id] => 0
     * [process_id] => 6
     * )
     * ...
     */
    private function transformRatingsArray($input)
    {
        $newCriteria = array_map(function ($criterion) use ($input) {
            return array_merge($criterion, [
                'quality_dimension_id' => $input['quality_dimension_id'],
                'question_id' => $input['question_id'],
                'process_id' => $input['process_id'],
            ]);
        }, $input['criteria']);

        return $newCriteria;
    }

    /**
     * Converts transformed criteria array from associative to numerically-indexed format.
     *
     * patchEntities expects a numerically-indexed array where keys correspond to entity positions,
     * but transformRatingsArray returns an associative array with question IDs as keys (e.g., 'DA-Z12').
     * This helper reindexes the array to match the entity collection structure.
     *
     * @param array $transformedCriteria Associative array with question IDs as keys
     * @return array Numerically-indexed array suitable for patchEntities
     */
    private function reindexCriteriaForPatch(array $transformedCriteria)
    {
        return array_values($transformedCriteria);
    }

    /**
     * Calculates navigation state for all quality dimensions.
     * Single source of truth for completion logic and URL generation.
     *
     * @param array $criteria Criteria grouped by quality_dimension_id => [question_id => ...]
     * @param array $AP_relevances Applikationsfragen relevances by quality_dimension_id
     * @param array $GF_relevances Grundfragen relevances by quality_dimension_id
     * @param int|null $process_id Process ID for URL generation (optional)
     * @return array Navigation state: ['qd_id' => ['isComplete' => bool, 'action' => string, 'url' => array, 'hasStarted' => bool]]
     */
    private function calculateNavigationState(array $criteria, array $AP_relevances, array $GF_relevances, ?int $process_id = null)
    {
        $navigationState = [];

        foreach ($this->protectionNeedsAnalysis as $qd_id => $qd) {
            $quality_dimension_id = $qd['quality_dimension_id'];

            // Determine completion status
            $isComplete = false;
            if (array_key_exists($quality_dimension_id, $criteria)) {
                if (!is_null($AP_relevances[$quality_dimension_id])) {
                    if (
                        $AP_relevances[$quality_dimension_id] === false
                        || $GF_relevances[$quality_dimension_id] === false
                        || count($criteria[$quality_dimension_id]) === 3
                        || ($qd_id === 'VE' && count($criteria[$quality_dimension_id]) === 2)
                    ) {
                        $isComplete = true;
                    }
                }
            }

            $hasStarted = array_key_exists($quality_dimension_id, $criteria);
            $action = $isComplete ? 'editRateQD' : 'rateQD';

            // Build URL if process_id provided
            $url = null;
            if ($process_id !== null) {
                $url = ['controller' => 'Criteria', 'action' => $action, 'process_id' => $process_id, 'qd_id' => $qd_id];
                if ($isComplete) {
                    $url['question_id'] = 0;  // Edit mode starts at first question
                }
            }

            $navigationState[$qd_id] = [
                'isComplete' => $isComplete,
                'hasStarted' => $hasStarted,
                'action' => $action,
                'url' => $url,
            ];
        }

        return $navigationState;
    }

    /**
     * Filters out questions that have receivesValueFromCriterion attribute.
     * These questions should not be displayed to users as they automatically receive values from other criteria.
     *
     * @param array $protectionNeedsAnalysis Protection needs analysis configuration
     * @return array Filtered configuration without receivesValueFromCriterion questions
     */
    private function filterReceivesValueFromCriterion(array $protectionNeedsAnalysis)
    {
        foreach ($protectionNeedsAnalysis as $qdId => &$qd) {
            if (isset($qd['questions'])) {
                foreach ($qd['questions'] as $questionTypeId => &$questions) {
                    $questions = array_filter($questions, function ($question) {
                        return !isset($question['receivesValueFromCriterion']);
                    });
                }
            }
        }

        return $protectionNeedsAnalysis;
    }

    /**
     * set all related criteria in $transformedCriteria to the same value
     * check if there are already related questions in the database and update them to the same value
     *
     * @param $transformedCriteria
     * @return array
     */
    private function handleRelatedQuestions($transformedCriteria)
    {
        $relatedQuestions = $this->Criteria->extractRelatedQuestions($this->protectionNeedsAnalysis);
        foreach ($transformedCriteria as $key => $values) {
            if (array_key_exists($key, $relatedQuestions)) {
                foreach ($relatedQuestions[$key] as $relatedQuestionKey) {
                    if (array_key_exists($relatedQuestionKey, $transformedCriteria)) {
                        // 1) set all related criteria in $transformedCriteria to the same value
                        $transformedCriteria[$relatedQuestionKey]['value'] = $values['value'];
                    } else {
                        // 2) set all related criteria in other RelatedQuestions to the same value
                        if ($this->Criteria->find('all')->where(['title' => $relatedQuestionKey, 'process_id' => $values['process_id']])->count() > 0) {
                            $relatedQuestion = $this->Criteria->find('all')->where(['title' => $relatedQuestionKey, 'process_id' => $values['process_id']])->first();
                            $transformedCriteria[$key]['value'] = $relatedQuestion['value'];
                        }
                    }
                }
            }
        }

        return $transformedCriteria;
    }

    /**
     * Notifies all project participants (project owner and examiners) about process updates.
     *
     * @param object $process Process entity with project and examiners associations
     * @param string $notificationTitle Title of the notification
     * @param string $notificationDescription Description/message of the notification
     * @return void
     */
    private function notifyProjectParticipants(object $process, string $notificationTitle, string $notificationDescription): void
    {
        $notificationModel = $this->fetchModel('Notifications');

        // Notify project owner
        $notificationModel->createNotification(
            $notificationTitle,
            $notificationDescription,
            $process->project->user_id,
            $process->id,
        );

        // Notify all examiners (if any)
        if (!empty($process->examiners)) {
            foreach ($process->examiners as $examiner) {
                $notificationModel->createNotification(
                    $notificationTitle,
                    $notificationDescription,
                    $examiner->id,
                    $process->id,
                );
            }
        }
    }

    /**
     * Gets navigation state data for a process.
     * Single source of truth for fetching criteria, relevances, and calculating navigation state.
     *
     * @param int $process_id Process ID
     * @return array Array containing: AP_relevances, GF_relevances, navigationState
     */
    private function getNavigationStateForProcess(int $process_id)
    {
        $criteriaGrouped = $this->Criteria->find(
            'list',
            keyField: 'question_id',
            valueField: 'quality_dimension_id',
            groupField: 'quality_dimension_id',
        )->where(['process_id' => $process_id])->toArray();

        $AP_relevances = $this->Criteria->checkRelevancesForAP($this->qualityDimensions, $process_id);
        $GF_relevances = $this->Criteria->checkRelevancesForGF($this->qualityDimensions, $process_id);
        $navigationState = $this->calculateNavigationState($criteriaGrouped, $AP_relevances, $GF_relevances, $process_id);

        return compact('AP_relevances', 'GF_relevances', 'navigationState');
    }
}
