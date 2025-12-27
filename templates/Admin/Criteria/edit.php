<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Criterion $criterion
 * @var \App\Controller\CriteriaController $qualityDimensionIds
 * @var \App\Controller\AppController $protectionTargetCategories
 * @var \App\Controller\AppController $criterionTypes
 * @var \App\Controller\AppController $questionTypes
 * @var string[]|\Cake\Collection\CollectionInterface $processes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Criteria'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="criteria form content">
            <?= $this->Form->create($criterion) ?>
            <fieldset>
                <legend><?= __d('admin', 'Edit Criterion') ?></legend>
                <?php
                    echo $this->Form->control('protection_target_category_id', ['options' => $protectionTargetCategories]);
                    echo $this->Form->control('quality_dimension_id', ['options' => $qualityDimensionIds]);
                    echo $this->Form->control('criterion_type_id', ['options' => $criterionTypes]);
                    echo $this->Form->control('question_id', ['options' => $questionTypes]);
                    echo $this->Form->control('process_id', ['options' => $processes]);
                    echo $this->Form->control('title');
                    echo $this->Form->control('value');
                ?>
            </fieldset>
            <?= $this->Form->button(__d('admin', 'Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
