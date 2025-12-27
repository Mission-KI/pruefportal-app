<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var \Cake\Collection\CollectionInterface|string[] $processes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Comments'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="comments form content">
            <?= $this->Form->create($comment) ?>
            <fieldset>
                <legend><?= __d('admin', 'Edit Comment') ?></legend>
                <?php
                    echo $this->Form->control('content');
                    echo $this->Form->control('seen');
                    echo $this->Form->control('reference_id', ['type' => 'text']);
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('process_id', ['options' => $processes, 'empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__d('admin', 'Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
