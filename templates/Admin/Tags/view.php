<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Tag'), ['action' => 'edit', $tag->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Tag'), ['action' => 'delete', $tag->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $tag->title), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Tags'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Tag'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="tags view content">
            <h3><?= h($tag->title) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($tag->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Title') ?></th>
                    <td><?= h($tag->title) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __d('admin', 'Related Users') ?></h4>
                <?php if (!empty($tag->users)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'Username') ?></th>
                            <th><?= __d('admin', 'Role') ?></th>
                            <th><?= __d('admin', 'Full Name') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($tag->users as $user) : ?>
                        <tr>
                            <td><?= h($user->id) ?></td>
                            <td><?= h($user->username) ?></td>
                            <td><?= h($user->role->name) ?></td>
                            <td><?= h($user->full_name) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Users', 'action' => 'view', $user->id]) ?>
                                <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Users', 'action' => 'edit', $user->id]) ?>
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
