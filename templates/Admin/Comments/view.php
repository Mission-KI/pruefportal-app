<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Comment'), ['action' => 'edit', $comment->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Comment'), ['action' => 'delete', $comment->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $comment->reference_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Comments'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Comment'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="comments view content">
            <h3><?= __d('admin', 'Comment') ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($comment->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Seen') ?></th>
                    <td><?= $this->element('admin_boolean', array('bool' => $comment->seen)); ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Reference') ?></th>
                    <td><?= $comment->reference_id > 0 ? h($comment->reference_id) : __d('admin', 'General') ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Parent Comment') ?></th>
                    <td><?= $comment->hasValue('parent_id') ? $this->Html->link(h($comment->parent_id) . '. ' . __d('admin', 'Comment'), ['action' => 'view', $comment->parent_id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'User') ?></th>
                    <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process') ?></th>
                    <td><?= $comment->hasValue('process') ? $this->Html->link($comment->process->title, ['controller' => 'Processes', 'action' => 'view', $comment->process->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($comment->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($comment->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __d('admin', 'Content') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($comment->content)); ?>
                </blockquote>
            </div>
            <?php if (!empty($comment->child_comments)) : ?>
            <div class="related">
                <h4><?= __d('admin', 'Related Child Comments') ?></h4>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'User') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($comment->child_comments as $childComment): ?>
                            <tr>
                                <td><?= $this->Html->link(h($childComment->id) .'. '. __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $childComment->id]) ?></td>
                                <td><?= $this->Html->link(h($childComment->user->username), ['controller' => 'Users', 'action' => 'view', $childComment->user_id]) ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Comments', 'action' => 'view', $childComment->id]) ?>
                                    <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Comments', 'action' => 'edit', $childComment->id]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            <div class="related">
                <h4><?= __d('admin', 'Related Uploads') ?></h4>
                <?php if (!empty($comment->uploads)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Name') ?></th>
                                <th><?= __d('admin', 'Created') ?></th>
                                <th><?= __d('admin', 'Modified') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($comment->uploads as $upload) : ?>
                                <tr>
                                    <td><?= h($upload->id) ?></td>
                                    <td><?= h($upload->name) ?></td>
                                    <td><?= h($upload->created) ?></td>
                                    <td><?= h($upload->modified) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Uploads', 'action' => 'view', $upload->id]) ?>
                                        <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Uploads', 'action' => 'edit', $upload->id]) ?>
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
