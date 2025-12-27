<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Notification> $notifications
 */
?>
<div class="notifications index content">
    <?= $this->Html->link(__d('admin', 'Add Notification'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Notifications') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('seen') ?></th>
                    <th><?= $this->Paginator->sort('mailed') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('process_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notification): ?>
                <tr>
                    <td><?= $this->Number->format($notification->id) ?></td>
                    <td><?= $this->Html->link(h($notification->title), ['action' => 'view', $notification->id]) ?></td>
                    <td><?= $this->element('admin_boolean', array('bool' => $notification->seen)); ?></td>
                    <td><?= $this->element('admin_boolean', array('bool' => $notification->mailed)); ?></td>
                    <td><?= $notification->hasValue('user') ? $this->Html->link($notification->user->username, ['controller' => 'Users', 'action' => 'view', $notification->user->id]) : '' ?></td>
                    <td><?= $notification->hasValue('process') ? $this->Html->link($notification->process->title, ['controller' => 'Processes', 'action' => 'view', $notification->process->id]) : '' ?></td>
                    <td><?= h($notification->created) ?></td>
                    <td><?= h($notification->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $notification->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $notification->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $notification->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $notification->title),
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
