<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\UsecaseDescription> $usecaseDescriptions
 */
?>
<div class="usecaseDescriptions index content">
    <?= $this->Html->link(__d('admin', 'Add Usecase Description'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Usecase Descriptions') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('step') ?></th>
                    <th><?= $this->Paginator->sort('version') ?></th>
                    <th><?= $this->Paginator->sort('process_id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usecaseDescriptions as $usecaseDescription): ?>
                <tr>
                    <td><?= $this->Number->format($usecaseDescription->id) ?></td>
                    <td><?= $this->Number->format($usecaseDescription->step) ?></td>
                    <td><?= $this->Number->format($usecaseDescription->version) ?></td>
                    <td><?= $usecaseDescription->hasValue('process') ? $this->Html->link($usecaseDescription->process->title, ['controller' => 'Processes', 'action' => 'view', $usecaseDescription->process->id]) : '' ?></td>
                    <td><?= $usecaseDescription->hasValue('user') ? $this->Html->link($usecaseDescription->user->username, ['controller' => 'Users', 'action' => 'view', $usecaseDescription->user->id]) : '' ?></td>
                    <td><?= h($usecaseDescription->created) ?></td>
                    <td><?= h($usecaseDescription->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $usecaseDescription->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $usecaseDescription->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $usecaseDescription->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $usecaseDescription->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __d('admin', 'first')) ?>
            <?= $this->Paginator->prev('< ' . __d('admin', 'previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__d('admin', 'next') . ' >') ?>
            <?= $this->Paginator->last(__d('admin', 'last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__d('admin', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
