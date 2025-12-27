<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\AppController $statuses
 * @var \App\Controller\AppController $questionTypes
 * @var \App\Controller\ProcessesController $qualityDimensionIds
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Process'), ['action' => 'edit', $process->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Process'), ['action' => 'delete', $process->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $process->title), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Processes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Process'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="processes view content">
            <h3><?= h($process->title) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($process->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Title') ?></th>
                    <td><?= h($process->title) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Project') ?></th>
                    <td><?= $process->hasValue('project') ? $this->Html->link($process->project->title, ['controller' => 'Projects', 'action' => 'view', $process->project->id]): '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Status') ?></th>
                    <td><?= $statuses[$this->Number->format($process->status_id)] ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Candidate User') ?></th>
                    <td><?= $process->hasValue('candidate') ? $this->Html->link($process->candidate->username, ['controller' => 'Users', 'action' => 'view', $process->candidate->id]): '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Examiner Users') ?></th>
                    <td>
                        <?php if (!empty($process->examiners)): ?>
                            <?php foreach ($process->examiners as $examiner): ?>
                                <?= $this->Html->link($examiner->username, ['controller' => 'Users', 'action' => 'view', $examiner->id]) ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($process->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($process->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __d('admin', 'Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($process->description)); ?>
                </blockquote>
            </div>

            <div class="related">
                <h4><?= __d('admin', 'Related Criteria') ?></h4>
            <?php
                if (!empty($process->criteria)):
                    echo $process->status_id >= 30 ? $this->Html->link(__d('admin', 'Criteria Calculation'), ['controller' => 'Criteria', 'action' => 'calculation', $process->id], ['class' => 'float-right button button-outline']) : '';
            ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'Question Type') ?></th>
                            <th><?= __d('admin', 'Title') ?></th>
                            <th><?= __d('admin', 'Value') ?></th>
                            <th><?= __d('admin', 'Quality Dimension') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($process->criteria as $criterion): ?>
                            <tr>
                                <td><?= h($criterion->id) ?></td>
                                <td><?= $questionTypes[$this->Number->format($criterion->question_id)] ?></td>
                                <td><?= $this->Html->link(h($criterion->title), ['controller' => 'Criteria', 'action' => 'view', $criterion->id]) ?></td>
                                <td><?= h($criterion->value) ?></td>
                                <td><?= $qualityDimensionIds[$this->Number->format($criterion->quality_dimension_id)] ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Criteria', 'action' => 'view', $criterion->id]) ?>
                                    <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Criteria', 'action' => 'edit', $criterion->id]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="related">
                <h4><?= __d('admin', 'Related Comments') ?></h4>
                <?php if (!empty($process->comments)): ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'Parent Comment') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($process->comments as $comment): ?>
                            <tr>
                                <td><?= $this->Html->link(h($comment->id) .'. '. __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $comment->id]) ?></td>
                                <td><?= $comment->parent_id ? $this->Html->link(h($comment->parent_id) .'. '. __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $comment->parent_id]): '' ?></td>
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
                <h4><?= __d('admin', 'Related Indicators') ?></h4>
                <?php if (!empty($process->indicators)): ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'Title') ?></th>
                            <th><?= __d('admin', 'Level Candidate') ?></th>
                            <th><?= __d('admin', 'Level Examiner') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($process->indicators as $indicator): ?>
                            <tr>
                                <td><?= h($indicator->id) ?></td>
                                <td><?= $this->Html->link(h($indicator->title), ['controller' => 'Indicators', 'action' => 'view', $indicator->id]) ?></td>
                                <td><?= $this->Number->format($indicator->level_candidate) ?></td>
                                <td><?= $indicator->level_examiner === null ? '' : $this->Number->format($indicator->level_examiner) ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'Indicators', 'action' => 'view', $indicator->id]) ?>
                                    <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'Indicators', 'action' => 'edit', $indicator->id]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="related">
                <h4><?= __d('admin', 'Related Notifications') ?></h4>
                <?php if (!empty($process->notifications)): ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('admin', 'Id') ?></th>
                            <th><?= __d('admin', 'Title') ?></th>
                            <th><?= __d('admin', 'Seen') ?></th>
                            <th><?= __d('admin', 'Mailed') ?></th>
                            <th class="actions"><?= __d('admin', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($process->notifications as $notification): ?>
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

            <div class="related">
                <h4><?= __d('admin', 'Related Usecase Descriptions') ?></h4>
                <?php if (!empty($process->usecase_descriptions)): ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Step') ?></th>
                                <th><?= __d('admin', 'Version') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($process->usecase_descriptions as $usecaseDescription): ?>
                                <tr>
                                    <td><?= h($usecaseDescription->id) ?></td>
                                    <td><?= $this->Number->format($usecaseDescription->step) ?></td>
                                    <td><?= $this->Number->format($usecaseDescription->version) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__d('admin', 'View'), ['controller' => 'UsecaseDescriptions', 'action' => 'view', $usecaseDescription->id]) ?>
                                        <?= $this->Html->link(__d('admin', 'Edit'), ['controller' => 'UsecaseDescriptions', 'action' => 'edit', $usecaseDescription->id]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="related">
                <h4><?= __d('admin', 'Related Uploads') ?></h4>
                <?php if (!empty($process->uploads)): ?>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th><?= __d('admin', 'Id') ?></th>
                                <th><?= __d('admin', 'Name') ?></th>
                                <th><?= __d('admin', 'Created') ?></th>
                                <th><?= __d('admin', 'Modified') ?></th>
                                <th class="actions"><?= __d('admin', 'Actions') ?></th>
                            </tr>
                            <?php foreach ($process->uploads as $upload): ?>
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
