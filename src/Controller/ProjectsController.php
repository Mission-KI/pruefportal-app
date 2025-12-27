<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ParticipantValidationTrait;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\FrozenTime;
use stdClass;

/**
 * Projects Controller
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 */
class ProjectsController extends AppController
{
    use ParticipantValidationTrait;

    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['overallAssessment']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $user = $this->request->getAttribute('identity');
        $query = $this->Projects->find('all', contain: ['Processes' => ['Candidates', 'Examiners', 'UsecaseDescriptions']])->where(['Projects.user_id' => $user->id]);
        $projects = $this->paginate($query);
        $this->set(compact('projects'));
    }

    /**
     * View method
     *
     * @param string|null $id Project id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $project = $this->Projects->get($id, contain: ['Processes' => ['Candidates', 'Examiners'], 'Users']);
        $this->set(compact('project'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $project = $this->Projects->newEmptyEntity();
        $user = $this->request->getAttribute('identity');
        $project->candidate_name = $user->full_name;
        $project->candidate_email = $user->email;
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

            $candidateName = $requestData['candidate_name'];
            $candidateEmail = $requestData['candidate_email'];

            // Extract process data (keep original values for restoration)
            $processData = [
                'title' => $requestData['process_title'] ?? null,
                'description' => $requestData['process_description'] ?? null,
                'candidate_name' => $candidateName,
                'candidate_email' => $candidateEmail,
                'examiner_name' => $requestData['examiner_name'] ?? null,
                'examiner_email' => $requestData['examiner_email'] ?? null,
                'additional_participants' => $requestData['additional_participants'] ?? null,
                'status_id' => 0,
            ];

            // Remove process fields from project data
            unset($requestData['process_title'], $requestData['process_description']);
            unset($requestData['candidate_name'], $requestData['candidate_email']);
            unset($requestData['examiner_name'], $requestData['examiner_email']);
            unset($requestData['additional_participants']);

            $project = $this->Projects->patchEntity($project, $requestData);
            $project->user_id = $this->request->getAttribute('identity')->id;

            if ($this->Projects->save($project)) {
                // Now create the process
                $processesTable = $this->fetchTable('Processes');
                $process = $processesTable->newEmptyEntity();

                // Add project_id to process data
                $processData['project_id'] = $project->id;

                // Validate and create candidate/examiner users if provided
                $user = $this->request->getAttribute('identity');
                $processData = $this->validateCandidateExaminerUser(
                    $processData,
                    $user->full_name,
                    $processData['title'],
                );

                $process = $processesTable->patchEntity($process, $processData);

                if ($processesTable->save($process, ['associated' => ['Examiners']])) {
                    $this->Flash->success(__('The Project and Process have been saved.'));

                    return $this->redirect([
                        'controller' => 'Processes',
                        'action' => 'start',
                        $process->id,
                    ]);
                } else {
                    // If process save fails, delete the project
                    $this->Projects->delete($project);
                    $this->Flash->error(__('The Process could not be saved. Please, try again.'));
                }
            } else {
                $this->Flash->error(__('The Project could not be saved. Please, try again.'));
            }

            // Restore form field values for re-rendering (use original processData)
            $originalRequest = $this->request->getData();
            $project->set('process_title', $originalRequest['process_title'] ?? '');
            $project->set('process_description', $originalRequest['process_description'] ?? '');
            $project->set('candidate_name', $originalRequest['candidate_name'] ?? '');
            $project->set('candidate_email', $originalRequest['candidate_email'] ?? '');
            $project->set('examiner_name', $originalRequest['examiner_name'] ?? '');
            $project->set('examiner_email', $originalRequest['examiner_email'] ?? '');
        }
        $this->set(compact('project'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Project id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $project = $this->Projects->get($id, contain: []);
        if ($project->user_id !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $project = $this->Projects->patchEntity($project, $this->request->getData());
            if ($this->Projects->save($project)) {
                $this->Flash->success(__('The Project has been saved.'));
            } else {
                $this->Flash->error(__('The Project could not be saved. Please, try again.'));
            }
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Project id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $project = $this->Projects->get($id);
        if ($project->user_id !== $this->request->getAttribute('identity')->id) {
            throw new ForbiddenException();
        }
        if ($this->Projects->delete($project)) {
            $this->Flash->success(__('The Project has been deleted.'));
        } else {
            $this->Flash->error(__('The Project could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function overallAssessment()
    {
        $mockProcess = new stdClass();
        $mockProcess->id = 'XXXXXXX';
        $mockProcess->title = 'HR-Kandidaten-Tool';
        $mockProcess->modified = new FrozenTime('2025-11-26');

        $mockProject = new stdClass();
        $mockProject->industry = 'Personaldienstleistung';
        $mockProject->location = 'Hamburg, DE';

        $mockCandidate = new stdClass();
        $mockCandidate->full_name = 'Andreas Halber';
        $mockCandidate->email = 'halber@megaki.com';

        $mockUsecaseDescription = new stdClass();
        $usecaseData = [
            ['step' => 1, 'value' => 'Vereinfachung der Kandidatenauswahl in Bewerbungsprozessen sowie Unterstützung von BewerberInnen vor, während und nach dem Auswahlverfahren.'],
            ['step' => 2, 'value' => 'Vorqualifikation von BewerberInnen auf Grundlage der zur Verfügung gestellten Informationen, Kontaktmanagement, Kommunikation mit Kandidaten in einem mehrstufigen Prozess.'],
        ];
        $mockUsecaseDescription->description = json_encode($usecaseData);

        $mockProcess->project = $mockProject;
        $mockProcess->candidate = $mockCandidate;
        $mockProcess->examiner = null;
        $mockProcess->usecase_descriptions = [$mockUsecaseDescription];

        $qualityDimensionsConfig = json_decode(
            file_get_contents(WWW_ROOT . 'js' . DS . 'json' . DS . 'ProtectionNeedsAnalysis.json'),
            true,
        );

        $assessmentResults = [
            10 => ['rating' => 3, 'protection_needs' => 2, 'passed' => true],
            20 => ['rating' => 2, 'protection_needs' => 2, 'passed' => true],
            30 => ['rating' => 2, 'protection_needs' => 3, 'passed' => true],
            40 => ['rating' => 3, 'protection_needs' => 1, 'passed' => true],
            50 => ['rating' => 3, 'protection_needs' => 1, 'passed' => true],
            60 => ['rating' => 2, 'protection_needs' => 1, 'passed' => true],
        ];

        $this->set(compact('mockProcess', 'qualityDimensionsConfig', 'assessmentResults'));
    }
}
