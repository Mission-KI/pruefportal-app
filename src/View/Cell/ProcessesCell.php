<?php
declare(strict_types=1);

namespace App\View\Cell;

use App\Utility\ProcessActionService;
use App\Utility\StringHelper;
use Cake\Log\Log;
use Cake\View\Cell;

class ProcessesCell extends Cell
{
    /**
     * Renders the cell for the given user's candidate processes.
     *
     * @param int $user_id The ID of the user whose candidate processes to render.
     * @return void
     */
    public function candidate(int $user_id): void
    {
        Log::error('=== ProcessesCell::candidate() called for user_id: ' . $user_id);

        $candidateProcesses = $this->fetchTable('Processes')
            ->find('candidate', candidate: $user_id)
            ->all();

        Log::error('Found ' . count($candidateProcesses) . ' candidate processes');

        $criteriaModel = $this->fetchTable('Criteria');

        // Enrich each process with the user who needs to act next
        foreach ($candidateProcesses as $process) {
            $process->acting_user_type = $this->determineActingUser($process->status_id, 'candidate');
            $process->acting_user = $this->getActingUser($process, $process->acting_user_type);

            // Add continue action for primary button
            $process->continue_action = ProcessActionService::getContinueAction($process, 'candidate');

            // Add risk level for processes past PNA
            if ($process->status_id >= 20) {
                $process->risk_level = $criteriaModel->calculateOverallRiskLevel($process->id);
            }
        }

        $this->set('candidateProcesses', $candidateProcesses);
    }

    /**
     * Renders the cell for the given user's examiner processes.
     *
     * @param int $user_id The ID of the user whose examiner processes to render.
     * @return void
     */
    public function examiner(int $user_id): void
    {
        $examinerProcesses = $this->fetchTable('Processes')
            ->find('examiner', examiner: $user_id)
            ->all();

        $criteriaModel = $this->fetchTable('Criteria');

        // Enrich each process with the user who needs to act next
        foreach ($examinerProcesses as $process) {
            $process->acting_user_type = $this->determineActingUser($process->status_id, 'examiner');
            $process->acting_user = $this->getActingUser($process, $process->acting_user_type);

            // Add continue action for primary button
            $process->continue_action = ProcessActionService::getContinueAction($process, 'examiner');

            // Add risk level for processes past PNA
            if ($process->status_id >= 20) {
                $process->risk_level = $criteriaModel->calculateOverallRiskLevel($process->id);
            }
        }

        $this->set('examinerProcesses', $examinerProcesses);
    }

    /**
     * Determines which user type needs to act based on process status
     *
     * @param int $status_id The process status ID
     * @param string $view_context Either 'candidate' or 'examiner' view
     * @return string Either 'examiner' or 'candidate'
     */
    private function determineActingUser(int $status_id, string $view_context): string
    {
        // Status 10, 20, 30, 35, 50: Candidate acts
        // Status 40: Examiner acts
        // Status 60: Complete - show opposite user from view context
        return match ($status_id) {
            10, 20, 30, 35, 50 => 'candidate',
            40 => 'examiner',
            60 => $view_context === 'examiner' ? 'candidate' : 'examiner',
            default => 'candidate'
        };
    }

    /**
     * Gets the user entity and role based on user type
     *
     * @param object $process The process entity
     * @param string $user_type Either 'examiner' or 'candidate'
     * @return array|null Array with 'entity', 'role', 'name_without_title', and 'initials' keys, or null if no user
     */
    private function getActingUser(object $process, string $user_type): ?array
    {
        $users = [];
        $role = '';

        if ($user_type === 'examiner' && !empty($process->examiners)) {
            foreach ($process->examiners as $examiner) {
                $nameWithoutTitle = $this->removeTitle($examiner->salutation_name ?? '');
                $users[] = [
                    'entity' => $examiner,
                    'name_without_title' => $nameWithoutTitle,
                    'initials' => $this->getInitials($nameWithoutTitle),
                ];
            }
            $role = __('Examiner');
        } elseif ($user_type === 'candidate' && $process->candidate) {
            $nameWithoutTitle = $this->removeTitle($process->candidate->salutation_name ?? '');
            $users[] = [
                'entity' => $process->candidate,
                'name_without_title' => $nameWithoutTitle,
                'initials' => $this->getInitials($nameWithoutTitle),
            ];
            $role = __('Candidate');
        }

        if (empty($users)) {
            return null;
        }

        return [
            'users' => $users,
            'role' => $role,
            'entity' => $users[0]['entity'],
            'name_without_title' => $users[0]['name_without_title'],
            'initials' => $users[0]['initials'],
        ];
    }

    /**
     * Removes salutation titles (Mr, Ms, Mrs, Dr, etc.) from name
     *
     * @param string $name Full name with potential title
     * @return string Name without title
     */
    private function removeTitle(string $name): string
    {
        return StringHelper::removeTitle($name);
    }

    /**
     * Extracts initials from full name (first letter of first and last name)
     * Strips emojis and variation selectors before extraction to avoid emoji words
     *
     * @param string $name Full name
     * @return string Initials (e.g., "John Doe" -> "JD")
     */
    private function getInitials(string $name): string
    {
        return StringHelper::getInitials($name);
    }

    public function participants($user_id, $process_id = null): void
    {
        // TODO: Backend team - review this data structure change for process grouping by project
        // Changed from flat array to nested array grouped by project title for optgroup rendering
        // https://github.com/Mission-KI/pruefportal/issues/139

        $query = $this->fetchTable('Processes')->find()
            ->contain(['Projects', 'Examiners'])
            ->leftJoinWith('Examiners', function ($q) use ($user_id) {
                return $q->where(['Examiners.id' => $user_id]);
            })
            ->where([
                'OR' => [
                    ['Projects.user_id' => $user_id],
                    ['candidate_user' => $user_id],
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

        // Flatten for process_id check (preserve keys with + operator)
        $flatProcesses = [];
        foreach ($processes as $groupProcesses) {
            $flatProcesses = $flatProcesses + $groupProcesses;
        }

        if (!$process_id) {
            $process_id = array_key_first($flatProcesses);
        }
        if ($process_id) {
            $participants = $this->fetchTable('Processes')->find('participants', process: (int)$process_id);
            $participants = $participants->first();
            $this->set(compact('participants', 'processes', 'process_id'));
        }
    }
}
