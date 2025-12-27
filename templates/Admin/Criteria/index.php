<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Criterion> $criteria
* @var \App\Controller\CriteriaController $qualityDimensionIds
 * @var \App\Controller\AppController $protectionTargetCategories
 * @var \App\Controller\AppController $questionTypes
 */
?>
<div class="criteria index content">
    <div class="float-right">
        <?= $this->Html->link(__d('admin', 'Add Criterion'), ['action' => 'add'], ['class' => 'button']) ?>
        <?= $this->Html->link(__d('admin', 'Criteria Calculation'), ['action' => 'calculation'], ['class' => 'button button-outline']) ?>
    </div>
    <h3><?= __d('admin', 'Criteria') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('quality_dimension_id', __d('admin', 'Quality Dimension')) ?></th>
                    <th><?= $this->Paginator->sort('question_id', __d('admin', 'Question Type')) ?></th>
                    <th><?= $this->Paginator->sort('process_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($criteria as $criterion): ?>
                <tr>
                    <td><?= $this->Number->format($criterion->id) ?></td>
                    <td><?= $this->Html->link(h($criterion->title), ['action' => 'view', $criterion->id]) ?></td>
                    <td><?= $qualityDimensionIds[$this->Number->format($criterion->quality_dimension_id)] ?></td>
                    <td><?= $questionTypes[$this->Number->format($criterion->question_id)] ?></td>
                    <td><?= $criterion->hasValue('process') ? $this->Html->link($criterion->process->title, ['controller' => 'Processes', 'action' => 'view', $criterion->process->id]) : '' ?></td>
                    <td><?= h($criterion->created) ?></td>
                    <td><?= h($criterion->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $criterion->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $criterion->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $criterion->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $criterion->title),
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
