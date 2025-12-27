<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Project $project
 * @var \App\Controller\AppController $statuses
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Project'), ['action' => 'edit', $project->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Project'), ['action' => 'delete', $project->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $project->title), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Projects'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Project'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="projects view content">
            <h3><?= h($project->title) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($project->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Title') ?></th>
                    <td><?= h($project->title) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'User') ?></th>
                    <td><?= $project->hasValue('user') ? $this->Html->link($project->user->username, ['controller' => 'Users', 'action' => 'view', $project->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($project->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($project->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __d('admin', 'Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($project->description)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __d('admin', 'Related Processes') ?></h4>
                <?php if (!empty($project->processes)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'Title') ?></th>
                            <th><?= __d('admin', 'Status') ?></th>
                            <th><?= __d('admin', 'Candidate User') ?></th>
                            <th><?= __d('admin', 'Examiner User') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($project->processes as $process) : ?>
                        <tr>
                            <td><?= h($process->id) ?></td>
                            <td><?= $this->Html->link(h($process->title), ['controller' => 'Processes', 'action' => 'view', $process->id]) ?></td>
                            <td><?= $statuses[$this->Number->format($process->status_id)] ?></td>
                            <td><?= $process->hasValue('candidate') ? $this->Html->link($process->candidate->username, ['controller' => 'Users', 'action' => 'view', $process->candidate->id]) : '' ?></td>
                            <td>
                                <?php if (!empty($process->examiners)): ?>
                                    <?php foreach ($process->examiners as $examiner): ?>
                                        <?= $this->Html->link($examiner->username, ['controller' => 'Users', 'action' => 'view', $examiner->id]) ?><br>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Processes', 'action' => 'view', $process->id]) ?>
                                <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Processes', 'action' => 'edit', $process->id]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
