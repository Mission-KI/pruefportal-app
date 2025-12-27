<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Upload> $uploads
 */
?>
<div class="uploads index content">
    <?= $this->Html->link(__d('admin', 'Add Upload'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Uploads') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('size') ?></th>
                    <th><?= $this->Paginator->sort('process_id') ?></th>
                    <th><?= $this->Paginator->sort('comment_id') ?></th>
                    <th><?= $this->Paginator->sort('indicator_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uploads as $upload): ?>
                <tr>
                    <td><?= $this->Number->format($upload->id) ?></td>
                    <td><span class="truncate"><?= h($upload->name) ?></span></td>
                    <td><?= $upload->size ?></td>
                    <td><?= $upload->hasValue('process') ? $this->Html->link($upload->process->title, ['controller' => 'Processes', 'action' => 'view', $upload->process->id]) : '' ?></td>
                    <td><?= ($upload->comment_id) ? $this->Html->link($upload->comment_id . '. ' . __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $upload->comment_id]) : '' ?></td>
                    <td><?= ($upload->indicator_id) ? $this->Html->link($upload->indicator->title, ['controller' => 'Indicators', 'action' => 'view', $upload->indicator_id]) : '' ?></td>
                    <td><?= h($upload->created) ?></td>
                    <td><?= h($upload->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $upload->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $upload->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $upload->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $upload->name),
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
