<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UsecaseDescription $usecaseDescription
 * @var string[]|\Cake\Collection\CollectionInterface $processes
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Usecase Descriptions'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="usecaseDescriptions form content">
            <?= $this->Form->create($usecaseDescription) ?>
            <fieldset>
                <legend><?= __d('admin', 'Edit Usecase Description') ?></legend>
                <?php
                    echo $this->Form->control('step');
                    echo $this->Form->control('version');
                    echo $this->Form->control('description');
                    echo $this->Form->control('process_id', ['options' => $processes]);
                    echo $this->Form->control('user_id', ['options' => $users]);
                ?>
            </fieldset>
            <?= $this->Form->button(__d('admin', 'Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
