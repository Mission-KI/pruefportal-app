<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \App\Controller\AppController $statuses
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit User'), ['action' => 'edit', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete User'), ['action' => 'delete', $user->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $user->username), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users view content">
            <h3><?= h($user->username) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($user->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Enabled') ?></th>
                    <td><?= $this->element('admin_boolean', array('bool' => $user->enabled)); ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Username') ?></th>
                    <td><?= h($user->username) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Role') ?></th>
                    <td><?= h($user->role->name) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Key') ?></th>
                    <td><?= h($user->key) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Salutation') ?></th>
                    <td><?= h($user->salutation?->label()) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Full Name') ?></th>
                    <td><?= h($user->full_name) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Company') ?></th>
                    <td><?= h($user->company) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process Updates') ?></th>
                    <td><?= $this->element('admin_boolean', array('bool' => $user->process_updates)); ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Comment Notifications') ?></th>
                    <td><?= $this->element('admin_boolean', array('bool' => $user->comment_notifications)); ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($user->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($user->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __d('admin', 'Related Projects') ?></h4>
                <?php if (!empty($user->projects)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Title') ?></th>
                                <th><?= __d('admin', 'Created') ?></th>
                                <th><?= __d('admin', 'Modified') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($user->projects as $project) : ?>
                                <tr>
                                    <td><?= h($project->id) ?></td>
                                    <td><?= h($project->title) ?></td>
                                    <td><?= h($project->created) ?></td>
                                    <td><?= h($project->modified) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Projects', 'action' => 'view', $project->id]) ?>
                                        <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Projects', 'action' => 'edit', $project->id]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __d('admin', 'Related Comments') ?></h4>
                <?php if (!empty($user->comments)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Parent Comment') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($user->comments as $comment) : ?>
                                <tr>
                                    <td><?= $this->Html->link(h($comment->id) .'. '. __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $comment->id]) ?></td>
                                    <td><?= $comment->parent_id ? $this->Html->link(h($comment->parent_id) .'. '. __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $comment->parent_id]) : '' ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Comments', 'action' => 'view', $comment->id]) ?>
                                        <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Comments', 'action' => 'edit', $comment->id]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __d('admin', 'Related Tags') ?></h4>
                <?php if (!empty($user->tags)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Title') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($user->tags as $tag) : ?>
                                <tr>
                                    <td><?= h($tag->id) ?></td>
                                    <td><?= $this->Html->link(h($tag->title), ['controller' => 'Tags', 'action' => 'view', $tag->id]) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Tags', 'action' => 'view', $tag->id]) ?>
                                        <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Tags', 'action' => 'edit', $tag->id]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __d('admin', 'Related Notifications') ?></h4>
                <?php if (!empty($user->notifications)) : ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Title') ?></th>
                                <th><?= __d('admin', 'Seen') ?></th>
                                <th><?= __d('admin', 'Mailed') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($user->notifications as $notification) : ?>
                                <tr>
                                    <td><?= h($notification->id) ?></td>
                                    <td><?= $this->Html->link(h($notification->title), ['controller' => 'Notifications', 'action' => 'view', $notification->id]) ?></td>
                                    <td><?= $this->element('admin_boolean', array('bool' => $notification->seen)); ?></td>
                                    <td><?= $this->element('admin_boolean', array('bool' => $notification->mailed)); ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Notifications', 'action' => 'view', $notification->id]) ?>
                                        <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Notifications', 'action' => 'edit', $notification->id]) ?>
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
