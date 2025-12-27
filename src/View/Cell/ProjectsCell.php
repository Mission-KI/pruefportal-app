<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

class ProjectsCell extends Cell
{
    public function admin_tree($statuses): void
    {
        $project = $this->fetchTable('Projects')->find('all', contain: ['Processes' => ['Candidates', 'Examiners'], 'Users'])->orderByDesc('Projects.modified');
        $this->set('project', $project->first());
        $this->set('statuses', $statuses);
    }
}
