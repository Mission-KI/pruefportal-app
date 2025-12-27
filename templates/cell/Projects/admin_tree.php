<?php
/**
 * @var App\View\Cell\ProjectsCell $project
 * @var \App\Controller\AppController $statuses
 */
if($project):
?>
<div class="tree">
    <h3><?php echo __d('admin', 'Last Modified Project'); ?></h3>
    <ul>
        <li>
            <div>
                <strong><?= $this->Html->link($project->title, ['controller' => 'Projects', 'action' => 'view', $project->id]) ?></strong>
                <?= $project->hasValue('user') ? '<br>' . $this->element('atoms/button', [
                    'label' => $project->user->full_name,
                    'url' => ['controller' => 'Users', 'action' => 'view', $project->user->id],
                    'variant' => 'link',
                    'size' => 'xs',
                    'class' => 'small',
                    'title' => $project->user->username
                ]) : '' ?>
            </div>
            <ul>
                <?php foreach ($project->processes as $process): ?>
                <li>
                    <div>
                        <strong><?= $this->Html->link($process->title, ['controller' => 'Processes', 'action' => 'view', $process->id]) ?></strong><br>
                        <small><?= __d('admin', 'Status') ?>: <?= $statuses[$this->Number->format($process->status_id)] ?></small>
                    </div>
                    <ul>
                        <li><div><?= __d('admin', 'Candidate User') ?>:<br><?= $process->hasValue('candidate') ? $this->Html->link($process->candidate->full_name, ['controller' => 'Users', 'action' => 'view', $process->candidate->id], ['title' => $process->candidate->username]) : '-' ?></div></li>
                        <li><div><?= __d('admin', 'Examiner Users') ?>:<br>
                            <?php if (!empty($process->examiners)): ?>
                                <?php foreach ($process->examiners as $examiner): ?>
                                    <?= $this->Html->link($examiner->full_name, ['controller' => 'Users', 'action' => 'view', $examiner->id], ['title' => $examiner->username]) ?><br>
                                <?php endforeach; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div></li>
                    </ul>
                </li>
                <?php endforeach; ?>
            </ul>
        </li>
    </ul>
</div>
<?php endif; ?>
