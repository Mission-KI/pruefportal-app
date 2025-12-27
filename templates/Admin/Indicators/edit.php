<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Indicator $indicator
 * @var string[]|\Cake\Collection\CollectionInterface $processes
 * @var \App\Controller\Admin\IndicatorsController $qualityDimensionIds
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Indicators'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="indicators form content">
            <?= $this->Form->create($indicator) ?>
            <fieldset>
                <legend><?= __d('admin', 'Edit Indicator') ?></legend>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('level_candidate');
                    echo $this->Form->control('level_examiner');
                    echo $this->Form->control('process_id', ['options' => $processes, 'empty' => true]);
                    echo $this->Form->control('quality_dimension_id', ['options' => $qualityDimensionIds]);
                    echo $this->Form->control('evidence');
                ?>
            </fieldset>
            <?= $this->Form->button(__d('admin', 'Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
