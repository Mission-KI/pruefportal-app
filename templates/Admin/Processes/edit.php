<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var string[]|\Cake\Collection\CollectionInterface $projects
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var \App\Controller\AppController $statuses
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Processes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="processes form content">
            <?= $this->Form->create($process) ?>
            <fieldset>
                <legend><?= __d('admin', 'Edit Process') ?></legend>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('description');
                    echo $this->Form->control('project_id', ['options' => $projects]);
                    echo $this->Form->control('status_id', ['options' => $statuses]);
                    echo $this->Form->control('candidate_user', ['options' => $users]);
                    echo $this->Form->control('examiners._ids', ['options' => $users, 'label' => __d('admin', 'Examiners'), 'multiple' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__d('admin', 'Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
