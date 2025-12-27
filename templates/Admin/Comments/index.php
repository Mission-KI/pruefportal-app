<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment $comments
 */
?>
<div class="comments index content">
    <?= $this->Html->link(__d('admin', 'Add Comment'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Comments') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('reference_id') ?></th>
                    <th><?= $this->Paginator->sort('seen') ?></th>
                    <th><?= $this->Paginator->sort('process_id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?= $this->Number->format($comment->id) ?></td>
                    <td><?= $this->Html->link($comment->reference_id > 0 ? h($comment->reference_id) : 'General', ['action' => 'view', $comment->id]) ?></td>
                    <td><?= $this->element('admin_boolean', array('bool' => $comment->seen)); ?></td>
                    <td><?= $comment->hasValue('process') ? $this->Html->link($comment->process->title, ['controller' => 'Processes', 'action' => 'view', $comment->process->id]) : '' ?></td>
                    <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                    <td><?= h($comment->created) ?></td>
                    <td><?= h($comment->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $comment->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $comment->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $comment->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $comment->reference_id),
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
