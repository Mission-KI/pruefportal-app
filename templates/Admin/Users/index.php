<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<div class="users index content">
    <?= $this->Html->link(__d('admin', 'Add User'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Users') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('enabled') ?></th>
                    <th><?= $this->Paginator->sort('username') ?></th>
                    <th><?= $this->Paginator->sort('role') ?></th>
                    <th><?= $this->Paginator->sort('salutation') ?></th>
                    <th><?= $this->Paginator->sort('full_name') ?></th>
                    <th><?= $this->Paginator->sort('company') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $this->Number->format($user->id) ?></td>
                    <td><?= $this->element('admin_boolean', array('bool' => $user->enabled)); ?></td>
                    <td><?= $this->Html->link(h($user->username), ['action' => 'view', $user->id]) ?></td>
                    <td><?= h($user->role->name) ?></td>
                    <td><?= h($user->salutation?->label()) ?></td>
                    <td><?= h($user->full_name) ?></td>
                    <td><?= h($user->company) ?></td>
                    <td><?= h($user->created) ?></td>
                    <td><?= h($user->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $user->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $user->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $user->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $user->username),
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
