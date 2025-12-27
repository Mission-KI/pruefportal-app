<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Indicator> $indicators
 * @var \App\Controller\Admin\IndicatorsController $qualityDimensionIds
 */
?>
<div class="indicators index content">
    <?= $this->Html->link(__d('admin', 'Add Indicator'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __d('admin', 'Indicators') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('process_id') ?></th>
                    <th><?= $this->Paginator->sort('quality_dimension_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __d('admin', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($indicators as $indicator): ?>
                <tr>
                    <td><?= $this->Number->format($indicator->id) ?></td>
                    <td><?= h($indicator->title) ?></td>
                    <td><?= $indicator->hasValue('process') ? $this->Html->link($indicator->process->title, ['controller' => 'Processes', 'action' => 'view', $indicator->process->id]) : '' ?></td>
                    <td><?= $qualityDimensionIds[$this->Number->format($indicator->quality_dimension_id)] ?></td>
                    <td><?= h($indicator->created) ?></td>
                    <td><?= h($indicator->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__d('admin', 'View'), ['action' => 'view', $indicator->id]) ?>
                        <?= $this->Html->link(__d('admin', 'Edit'), ['action' => 'edit', $indicator->id]) ?>
                        <?= $this->Form->postLink(
                            __d('admin', 'Delete'),
                            ['action' => 'delete', $indicator->id],
                            [
                                'method' => 'delete',
                                'confirm' => __d('admin', 'Are you sure you want to delete {0}?', $indicator->title),
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
