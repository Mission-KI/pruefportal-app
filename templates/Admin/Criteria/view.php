<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Criterion $criterion
 * @var \App\Controller\CriteriaController $qualityDimensionIds
 * @var \App\Controller\AppController $protectionTargetCategories
 * @var \App\Controller\AppController $criterionTypes
 * @var \App\Controller\AppController $questionTypes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Criterion'), ['action' => 'edit', $criterion->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Criterion'), ['action' => 'delete', $criterion->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $criterion->title), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Criteria'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Criterion'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="criteria view content">
            <h3><?= h($criterion->title) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($criterion->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Title') ?></th>
                    <td><?= h($criterion->title) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process') ?></th>
                    <td><?= $criterion->hasValue('process') ? $this->Html->link($criterion->process->title, ['controller' => 'Processes', 'action' => 'view', $criterion->process->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Quality Dimension') ?></th>
                    <td><?= $qualityDimensionIds[$this->Number->format($criterion->quality_dimension_id)] ?> (<?= $this->Number->format($criterion->quality_dimension_id) ?>)</td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Value') ?></th>
                    <td><?= $this->Number->format($criterion->value) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Criterion Type') ?></th>
                    <td><?= $criterionTypes[$this->Number->format($criterion->criterion_type_id)] ?> (<?= $this->Number->format($criterion->criterion_type_id) ?>)</td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Question Type') ?></th>
                    <td><?= $questionTypes[$this->Number->format($criterion->question_id)] ?> (<?= $this->Number->format($criterion->question_id) ?>)</td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Protection Target Category') ?></th>
                    <td><?= $protectionTargetCategories[$this->Number->format($criterion->protection_target_category_id)] ?> (<?= $this->Number->format($criterion->protection_target_category_id) ?>)</td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($criterion->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($criterion->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
