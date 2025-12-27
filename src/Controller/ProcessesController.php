<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ParticipantValidationTrait;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use CakePdf\View\PdfView;

/**
 * Processes Controller
 *
 * @property \App\Model\Table\ProcessesTable $Processes
 */
class ProcessesController extends AppController
{
    use ModelAwareTrait;
    use ParticipantValidationTrait;

    public function initialize(): void
    {
        parent::initialize();

        // https://book.cakephp.org/5/en/controllers.html#content-type-negotiation
        $this->addViewClasses([PdfView::class]);
    }

    /**
     * Display threaded comments for a process.
     *
     * @param int|null $process_id Process ID to filter comments by.
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function comments(?int $process_id = null)
    {
        // TODO: Backend team - review this data structure change for process grouping by project
        // Changed from flat array to nested array grouped by project title for optgroup rendering
        // https://github.com/Mission-KI/pruefportal/issues/139

        // Find all processes the current user is involved in
        $userId = $this->Authentication->getIdentity()->id;

        $query = $this->Processes->find()
            ->contain(['Projects', 'Examiners'])
            ->leftJoinWith('Examiners', function ($q) use ($userId) {
                return $q->where(['Examiners.id' => $userId]);
            })
            ->where([
                'status_id >' => 0,
                'OR' => [
                    ['candidate_user' => $userId],
                    ['Examiners.id IS NOT' => null],
                ],
            ])
            ->distinct(['Processes.id'])
            ->order(['Processes.id' => 'ASC', 'Processes.modified' => 'DESC']);

        // Build grouped array: [project_title => [process_id => process_title]]
        $processes = [];
        foreach ($query as $process) {
            $projectTitle = $process->project->title ?? 'Unknown Project';
            if (!isset($processes[$projectTitle])) {
                $processes[$projectTitle] = [];
            }
            $processes[$projectTitle][$process->id] = $process->title;
        }

        // Flatten for userProcesses check (preserve keys with + operator)
        $flatProcesses = [];
        foreach ($processes as $groupProcesses) {
            $flatProcesses = $flatProcesses + $groupProcesses;
        }
        $userProcesses = $flatProcesses;

        if ($process_id) {
            if (!array_key_exists($process_id, $flatProcesses)) {
                throw new NotFoundException();
            }
            $userProcesses = [$process_id => $flatProcesses[$process_id]];
        } elseif (count($flatProcesses) > 0) {
            // Redirect to the latest Process.Comments
            $this->redirect(['action' => 'comments', array_key_first($flatProcesses)]);
        }

        if (!empty($userProcesses)) {
            // Find all top-level comments (where parent_id IS NULL) for the processes
            $comments = $this->Processes->Comments
                ->find('threaded', keyField: 'id', parentField: 'parent_id', order: ['Comments.created' => 'ASC'])
                ->contain(['Users', 'Processes', 'ChildComments.Users', 'Uploads'])
                ->where([
                    'Comments.process_id IN' => array_keys($userProcesses),
                    'Comments.parent_id IS' => null, // Only get top-level comments
                ])
                ->orderByDesc('Comments.created'); // Newest first

            // Group comments by process_id
            $groupedComments = [];
            foreach ($comments as $comment) {
                $processId = $comment->process_id;
                if (!isset($groupedComments[$processId])) {
                    $groupedComments[$processId] = [
                        'process' => $comment->process,
                        'comments' => [],
                    ];
                }
                $groupedComments[$processId]['comments'][] = $comment;
            }
        } else {
            $groupedComments = [];
        }

        $this->set(compact('groupedComments', 'processes', 'process_id'));
    }

    /**
     * View method
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $process = $this->Processes->get($id, contain: ['Projects', 'Candidates', 'Examiners', 'UsecaseDescriptions']);

        // Authorization check - user must be project owner, examiner, or candidate
        $identity = $this->request->getAttribute('identity');
        $isAuthorized = $process->project->user_id === $identity->id ||
                        $process->candidate_user === $identity->id ||
                        $process->isUserExaminer($identity->id);

        if (!$isAuthorized) {
            throw new ForbiddenException();
        }

        $this->set(compact('process'));
    }

    /**
     * Filter participants or comments by process.
     *
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Http\Exception\ForbiddenException When not a post request
     */
    public function filterProject()
    {
        if ($this->request->is('post')) {
            $process_id = $this->request->getData('process_id');
            $process_id = $process_id ? (int)$process_id : null;
            if ($this->request->getData('redirect') === 'comments') {
                $this->redirect(['action' => 'comments', $process_id]);
            }
            $this->set(compact('process_id'));
            $this->set('hasProcesses', true);

            return $this->render('/Pages/home');
        } else {
            throw new ForbiddenException();
        }
    }

    /**
     * Add method
     *
     * @param string|null $project_id Project id.
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(?string $project_id = null)
    {
        $project = $this->Processes->Projects->get($project_id, contain: []);
        if (!$project) {
            throw new NotFoundException();
        }
        $user = $this->request->getAttribute('identity');
        if ($project->user_id !== $user->id) {
            throw new ForbiddenException();
        }
        $process = $this->Processes->newEmptyEntity();
        $process->candidate_name = $user->full_name;
        $process->candidate_email = $user->email;
        if ($this->request->is('post')) {
            $requestData = $this->request->getData();

            // Fallback: If candidate fields are empty, use current user
            if (empty($requestData['candidate_name'])) {
                $requestData['candidate_name'] = $user->full_name;
            }
            if (empty($requestData['candidate_email'])) {
                $requestData['candidate_email'] = $user->username; // Email is stored in username field
            }

            $this->validateCandidateIsCurrentUser($requestData);
            $processData = $this->validateCandidateExaminerUser($requestData, $user->full_name, $requestData['title']);
            $process = $this->Processes->patchEntity($process, $processData);
            $process->status_id = 0; // $this->statuses

            if ($this->Processes->save($process)) {
                $this->Flash->success(__('The Process has been saved.'));

                return $this->redirect([
                    'controller' => 'Processes',
                    'action' => 'start',
                    $process->id,
                ]);
            }
            $this->Flash->error(__('The Process could not be saved. Please, try again.'));

            // Restore form field values for re-rendering
            $process->set('candidate_name', $requestData['candidate_name'] ?? '');
            $process->set('candidate_email', $requestData['candidate_email'] ?? '');
            $process->set('examiner_name', $requestData['examiner_name'] ?? '');
            $process->set('examiner_email', $requestData['examiner_email'] ?? '');
        }
        $this->set(compact('process', 'project'));
    }

    public function start($id = null)
    {
        $process = $this->Processes->get($id, [
            'contain' => ['Projects', 'Examiners'],
        ]);

        $user = $this->request->getAttribute('identity');
        if ($process->candidate_user !== $user->id) {
            throw new ForbiddenException(__('Only the candidate can start this process.'));
        }

        if ($process->status_id !== 0) {
            return $this->redirect(['action' => 'view', $id]);
        }

        $this->set(compact('process'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response Redirects to view action.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $process = $this->Processes->get($id, contain: ['Projects', 'Candidates', 'Examiners']);
        $user = $this->request->getAttribute('identity');
        if ($process->project->user_id !== $user->id) {
            throw new ForbiddenException();
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $requestData = $this->request->getData();
            $requestData = $this->validateCandidateExaminerUser($requestData, $user->full_name, $requestData['title']);
            $process = $this->Processes->patchEntity($process, $requestData);

            if ($this->Processes->save($process)) {
                $this->Flash->success(__('The Process has been saved.'));
            } else {
                $this->Flash->error(__('The Process could not be saved. Please, try again.'));
            }
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Add a participant to a process
     *
     * TODO: Current schema only supports one candidate and one examiner per process.
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null Redirects to view action.
     */
    public function addParticipant(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $process_id = $id ?? $this->request->getData('process');

        if (!$process_id) {
            $this->Flash->error(__('Process ID is required'));

            return $this->redirect($this->referer());
        }

        $process = $this->Processes->get($process_id, contain: ['Projects', 'Candidates', 'Examiners']);

        $identity = $this->request->getAttribute('identity');
        if ($process->project->user_id !== $identity->id) {
            throw new ForbiddenException();
        }

        $name = $this->request->getData('name');
        $email = $this->request->getData('email');
        $role = $this->request->getData('role');

        if (!in_array($role, ['candidate', 'examiner'])) {
            $this->Flash->error(__('Ungültige Rolle'));

            return $this->redirect($this->referer());
        }

        $subject = __(
            'Invite {0} user Subject from Project Owner: {1} for Project: {2}',
            ucfirst($role),
            $identity->full_name,
            $process->title,
        );

        $userModel = $this->fetchModel('Users');
        $user_id = $userModel->getCandidateExaminerUserId($email, $name, $subject);

        if ($role === 'examiner') {
            // Check if examiner already assigned
            if (isset($process->examiners) && !empty($process->examiners)) {
                $examinerIds = array_column($process->examiners, 'id');
                if (in_array($user_id, $examinerIds)) {
                    $this->Flash->error(__('Dieser Benutzer ist bereits Prüfer'));

                    return $this->redirect($this->referer());
                }
            }

            // Check if candidate is being added as examiner
            if ($process->candidate_user && $process->candidate_user === $user_id) {
                $this->Flash->error(__('Prüfling und Prüfer dürfen nicht dieselbe Person sein'));

                return $this->redirect($this->referer());
            }

            // Add to examiners collection using _ids for belongsToMany
            $existingIds = [];
            if (!empty($process->examiners)) {
                $existingIds = array_column($process->examiners, 'id');
            }
            $existingIds[] = $user_id;

            // Use patchEntity to properly handle _ids convention
            $process = $this->Processes->patchEntity($process, [
                'examiners' => ['_ids' => $existingIds],
            ]);
        } else {  // candidate
            $roleField = $role . '_user';
            if ($process->$roleField) {
                $this->Flash->error(__('Rolle ist für diesen Prozess bereits vergeben'));

                return $this->redirect($this->referer());
            }

            // Check if any examiner matches candidate email
            if (isset($process->examiners) && !empty($process->examiners)) {
                foreach ($process->examiners as $examiner) {
                    if (strtolower($examiner->username) === strtolower($email)) {
                        $this->Flash->error(__('Prüfling und Prüfer dürfen nicht dieselbe E-Mail-Adresse haben'));

                        return $this->redirect($this->referer());
                    }
                }
            }

            $process->$roleField = $user_id;
        }

        if ($this->Processes->save($process, ['associated' => ['Examiners']])) {
            $this->Flash->success(__('Prozessbeteiligte*r wurde hinzugefügt'));
        } else {
            $this->Flash->error(__('Prozessbeteiligte*r konnte nicht hinzugefügt werden. Bitte versuchen Sie es später noch einmal.'));
        }

        return $this->redirect($this->referer());
    }

    /**
     * Remove a User from a Process
     *
     * @param string|null $id Process id.
     * @param string $user_type 'candidate' or 'examiner' to specify which user to remove.
     * @return \Cake\Http\Response|null Redirects to view action.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\ForbiddenException When user is not allowed to remove the user.
     */
    public function removeUser(?string $id, string $user_type)
    {
        $process = $this->Processes->get($id, contain: ['Projects', 'Examiners']);

        if ($process->project->user_id !== $this->request->getAttribute('identity')->id) {
            $this->Flash->error(__('Sie haben nicht die notwendige Berechtigung, um Beteiligte aus diesem Prozess zu entfernen'));

            return $this->redirect($this->referer());
        }

        if ($process->status_id > 0) {
            $this->Flash->error(__('Bei einem laufenden Prozess dürfen Beteiligte nicht entfernt werden'));

            return $this->redirect($this->referer());
        }

        if ($user_type === 'candidate') {
            $process->candidate_user = null;
        }
        if ($user_type === 'examiner') {
            $user_id = $this->request->getQuery('user_id');
            if (!$user_id) {
                throw new BadRequestException('Missing user_id parameter');
            }

            // Filter out the examiner to remove
            $remainingExaminerIds = array_filter(
                array_column($process->examiners, 'id'),
                fn($id) => $id !== (int)$user_id,
            );

            // Use patchEntity with _ids format for belongsToMany
            $process = $this->Processes->patchEntity($process, [
                'examiners' => ['_ids' => array_values($remainingExaminerIds)],
            ]);
        }
        $this->Processes->save($process, ['associated' => ['Examiners']]);
        $this->Flash->success(__('The User has been removed from the Process.'));

        return $this->redirect($this->referer());
    }

    /**
     * Delete method
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $process = $this->Processes->get($id, contain: ['Projects']);
        if ($process->project->user_id !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }
        if ($this->Processes->delete($process)) {
            $this->Flash->success(__('The Process has been deleted.'));
        } else {
            $this->Flash->error(__('The Process could not be deleted. Please, try again.'));
        }

        return $this->redirect(['controller' => 'Projects', 'action' => 'index']);
    }

    /**
     * Start the process and redirect to Use Case Description creation.
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null Redirects to Use Case Description form.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\ForbiddenException When user is not authorized.
     */
    public function startProcess(?string $id = null)
    {
        $process = $this->Processes->get($id, contain: ['Projects']);
        $user = $this->request->getAttribute('identity');

        if ($process->candidate_user !== $user->id || $process->status_id !== 0) {
            throw new ForbiddenException();
        }

        $process->status_id = 10;

        $notification = $this->fetchModel('Notifications');
        $desc = __('Die Prüfung für "{0}" ist gestartet worden.', $process->title);
        $notification->createNotification(
            __('Notification: Prüfung gestartet'),
            $desc,
            $process->candidate_user,
            $process->id,
        );

        if ($this->Processes->save($process)) {
            $this->Flash->success(__('Der Prüfprozess wurde gestartet.'));

            return $this->redirect([
                'controller' => 'UsecaseDescriptions',
                'action' => 'add',
                $id,
            ]);
        } else {
            $this->Flash->error(__('The Process could not be saved. Please, try again.'));

            return $this->redirect($this->referer());
        }
    }

    /**
     * Show the total result of a process.
     *
     * @param string|null $id Process id.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function totalResult(?string $id = null)
    {
        $this->getTotalResultData($id);
    }

    public function download($id = null)
    {
        $this->viewBuilder()->enableAutoLayout(false);
        $this->getTotalResultData($id);
        $this->viewBuilder()->setClassName('CakePdf.Pdf');
        $this->viewBuilder()->setOption(
            'pdfConfig',
            [
                'orientation' => 'portrait',
                'download' => true, // This can be omitted if "filename" is specified.
                'filename' => date('Y-m-d') . '_process_' . $id . '.pdf', // This can be omitted if you want a file name based on URL.
                'options' => [
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                ],
            ],
        );
    }

    private function getTotalResultData($process_id)
    {
        $process = $this->Processes->get(
            $process_id,
            select: [
                'Processes.id',
                'Processes.candidate_user',
                'Processes.project_id',
                'Processes.status_id',
                'Processes.modified',
                'Processes.title',
                'Processes.description',
                'Projects.title',
                'Candidates.full_name',
            ],
            contain: [
                'Projects' => ['Users' => ['fields' => ['id', 'username', 'full_name']]],
                'Candidates' => ['fields' => ['full_name']],
                'Examiners' => ['fields' => ['full_name']],
            ],
        );
        if ($process->status_id < 60) { // $this->statuses
            throw new ForbiddenException();
        }

        $ucd = $this->Processes->UsecaseDescriptions->find()
            ->select(['description'])
            ->where(['process_id' => $process_id])
            ->orderByAsc('version')
            ->first();
        $ucd = json_decode('[' . $ucd->description . ']', true);
        if (count($ucd) > 0 && isset($ucd[0])) {
            $ucd = $ucd[0];
        }

        $qualityDimensionsConfig = $this->Processes->Indicators->getVcioConfig();

        $indicatorsSummary = $this->Processes->Indicators->find('list', keyField: 'quality_dimension_id', valueField: 'level_examiner')
            ->where(['process_id' => $process_id])
            ->orderBy('Indicators.quality_dimension_id')
            ->toArray();

        $criteria = $this->Processes->Criteria->find('list', keyField: 'quality_dimension_id', valueField: 'value')
            ->where(['process_id' => $process_id])
            ->orderBy('Criteria.quality_dimension_id')
            ->toArray();

        $indicators = $this->Processes->Indicators->find()->where([
            'process_id' => $process_id,
            'version' => 2,
            'level_examiner IS NOT' => null,
        ])->toArray();

        $nestedRelevances = $this->Processes->Criteria->calculateRelevances($process_id, $this->criterionTypes);

        $protectionLevelsByCriterionType = [];
        foreach ($nestedRelevances as $qd_id => $criterionTypesNested) {
            foreach ($criterionTypesNested as $criterionTypeId => $protectionLevel) {
                $protectionLevelsByCriterionType[$criterionTypeId] = $protectionLevel;
            }
        }

        $resultCandidate = $this->Processes->Indicators->calculateVcioClassification(
            $indicators,
            $qualityDimensionsConfig,
            $protectionLevelsByCriterionType,
            'level_candidate',
        );
        $classificationCandidate = $resultCandidate['classification'];

        $resultExaminer = $this->Processes->Indicators->calculateVcioClassification(
            $indicators,
            $qualityDimensionsConfig,
            $protectionLevelsByCriterionType,
            'level_examiner',
        );
        $classification = $resultExaminer['classification'];
        $fulfillment = $resultExaminer['fulfillment'];
        $protectionLevels = $resultExaminer['protectionLevels'];

        $qualityDimensionsData = $this->Processes->Indicators->normalizeForQualityDimensionsTable(
            $qualityDimensionsConfig,
            $indicators,
            $classification,
            $fulfillment,
            $protectionLevels,
            $this->criterionTypes,
            $process,
            $classificationCandidate,
        );

        $classificationToLevel = ['D' => 0, 'C' => 1, 'B' => 2, 'A' => 3, 'N/A' => null];
        $levelToClassification = [0 => 'D', 1 => 'C', 2 => 'B', 3 => 'A'];

        $qualityDimensionsSummary = [];
        foreach ($qualityDimensionsConfig as $code => $dimension) {
            $qdCriteria = $qualityDimensionsData[$code]['criteria'] ?? [];

            $maxProtectionLevel = 0;
            $minClassificationLevel = null;
            $hasCriteria = !empty($qdCriteria);

            foreach ($qdCriteria as $criterion) {
                $pl = $criterion['protectionLevel'] ?? null;
                if ($pl !== null && $pl > $maxProtectionLevel) {
                    $maxProtectionLevel = $pl;
                }

                $cl = $criterion['classification'] ?? 'N/A';
                $clLevel = $classificationToLevel[$cl] ?? null;
                if ($clLevel !== null) {
                    if ($minClassificationLevel === null || $clLevel < $minClassificationLevel) {
                        $minClassificationLevel = $clLevel;
                    }
                }
            }

            $protectionNeeds = $maxProtectionLevel;
            $rating = $minClassificationLevel ?? 0;
            $notRated = !$hasCriteria || $minClassificationLevel === null;
            $passed = $hasCriteria && $minClassificationLevel !== null && $rating >= $protectionNeeds;

            $qualityDimensionsSummary[$code] = [
                'protectionNeeds' => $protectionNeeds,
                'rating' => $rating,
                'ratingLabel' => $levelToClassification[$rating] ?? 'N/A',
                'notRated' => $notRated,
                'passed' => $passed,
                'ratingPercentage' => ($rating / 3) * 100,
                'protectionPercentage' => ($protectionNeeds / 3) * 100,
            ];
        }

        $this->set(compact(
            'process',
            'qualityDimensionsConfig',
            'criteria',
            'indicatorsSummary',
            'ucd',
            'qualityDimensionsData',
            'qualityDimensionsSummary'
        ));
    }
}
