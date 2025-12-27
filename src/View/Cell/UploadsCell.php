<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * Uploads cell
 */
class UploadsCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string, mixed>
     */
    protected array $_validCellOptions = [];

    /**
     * Display notifications for a user.
     *
     * @param int|null $user_id User ID to filter notifications by.
     * @return void
     */
    public function display(?int $user_id, $process_id = null): void
    {
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
        if (!$process_id) {
            return;
        }
        $uploads = $this->fetchTable('Uploads')->find('byProcess', process: (int)$process_id);
        $this->set('uploads', $uploads->toArray());
        $this->set(compact('processes', 'process_id'));
    }
}
