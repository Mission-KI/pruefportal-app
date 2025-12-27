<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Project> $projects
 */
?>
<div class="projects index content">
    <?= $this->Html->link(__d('admin', 'Add Project'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Projects') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
            <tr>
                <th><?= $this->Paginator->sort('id') ?></th>
                <th><?= $this->Paginator->sort('title') ?></th>
                <th><?= $this->Paginator->sort('user_id') ?></th>
                <th><?= $this->Paginator->sort('created') ?></th>
                <th><?= $this->Paginator->sort('modified') ?></th>
                <th class="actions"><?= __d('admin', 'Actions') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= $this->Number->format($project->id) ?></td>
                    <td><?= $this->Html->link(h($project->title), ['action' => 'view', $project->id]) ?></td>
                    <td><?= $project->hasValue('user') ? $this->Html->link($project->user->username, ['controller' => 'Users', 'action' => 'view', $project->user->id]) : '' ?></td>
                    <td><?= h($project->created) ?></td>
                    <td><?= h($project->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $project->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $project->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $project->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $project->title),
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
