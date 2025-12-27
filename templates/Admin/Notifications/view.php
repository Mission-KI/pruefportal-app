<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notification $notification
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Notification'), ['action' => 'edit', $notification->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Notification'), ['action' => 'delete', $notification->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $notification->title), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Notifications'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Notification'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="notifications view content">
            <h3><?= h($notification->title) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($notification->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Title') ?></th>
                    <td><?= h($notification->title) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Seen') ?></th>
                    <td><?= $this->element('admin_boolean', array('bool' => $notification->seen)); ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Mailed') ?></th>
                    <td><?= $this->element('admin_boolean', array('bool' => $notification->mailed)); ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'User') ?></th>
                    <td><?= $notification->hasValue('user') ? $this->Html->link($notification->user->username, ['controller' => 'Users', 'action' => 'view', $notification->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process') ?></th>
                    <td><?= $notification->hasValue('process') ? $this->Html->link($notification->process->title, ['controller' => 'Processes', 'action' => 'view', $notification->process->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($notification->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($notification->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __d('admin', 'Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($notification->description)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
