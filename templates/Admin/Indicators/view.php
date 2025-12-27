<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Indicator $indicator
 * @var \App\Controller\Admin\IndicatorsController $qualityDimensionIds
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Indicator'), ['action' => 'edit', $indicator->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Indicator'), ['action' => 'delete', $indicator->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $indicator->title), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Indicators'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Indicator'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="indicators view content">
            <h3><?= h($indicator->title) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($indicator->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Title') ?></th>
                    <td><?= h($indicator->title) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process') ?></th>
                    <td><?= $indicator->hasValue('process') ? $this->Html->link($indicator->process->title, ['controller' => 'Processes', 'action' => 'view', $indicator->process->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Quality Dimension') ?></th>
                    <td><?= $qualityDimensionIds[$this->Number->format($indicator->quality_dimension_id)] ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Level Candidate') ?></th>
                    <td><?= $this->Number->format($indicator->level_candidate) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Level Examiner') ?></th>
                    <td><?= $indicator->level_examiner === null ? '' : $this->Number->format($indicator->level_examiner) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($indicator->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($indicator->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __d('admin', 'Evidence') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($indicator->evidence)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
