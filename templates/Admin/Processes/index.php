<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Process> $processes
 * @var \App\Controller\AppController $statuses
 */
?>
<div class="processes index content">
    <?= $this->Html->link(__d('admin', 'Add Process'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Processes') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('project_id') ?></th>
                    <th><?= $this->Paginator->sort('status_id') ?></th>
                    <th><?= $this->Paginator->sort('candidate_user') ?></th>
                    <th><?= __d('admin', 'Examiner Users') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($processes as $process): ?>
                <tr>
                    <td><?= $this->Number->format($process->id) ?></td>
                    <td><?= $this->Html->link(h($process->title), ['action' => 'view', $process->id]) ?></td>
                    <td><?= $process->hasValue('project') ? $this->Html->link($process->project->title, ['controller' => 'Projects', 'action' => 'view', $process->project->id]) : '' ?></td>
                    <td><?= $statuses[$this->Number->format($process->status_id)] ?></td>
                    <td><?= $process->hasValue('candidate') ? $this->Html->link($process->candidate->username, ['controller' => 'Users', 'action' => 'view', $process->candidate->id]) : '' ?></td>
                    <td>
                        <?php if (!empty($process->examiners)): ?>
                            <?php foreach ($process->examiners as $examiner): ?>
                                <?= $this->Html->link($examiner->username, ['controller' => 'Users', 'action' => 'view', $examiner->id]) ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td><?= h($process->created) ?></td>
                    <td><?= h($process->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $process->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $process->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $process->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $process->title),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->element('admin_pagination'); ?>
</div>
