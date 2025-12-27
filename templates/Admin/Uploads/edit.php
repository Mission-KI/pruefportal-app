<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Upload $upload
 * @var string[]|\Cake\Collection\CollectionInterface $processes
 * @var string[]|\Cake\Collection\CollectionInterface $comments
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Uploads'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="uploads form content">
            <?= $this->Form->create($upload, ['enctype' => 'multipart/form-data']) ?>
            <fieldset>
                <legend><?= __d('admin', 'Edit Upload') ?></legend>
                <?php
                    echo $this->Form->control('key', ['disabled' => true]);
                    echo $this->Form->control('name');
                    echo $this->Form->control('size', ['disabled' => true]);
                    echo $this->Form->control('location', ['disabled' => true]);
                    echo $this->Form->control('etag', ['disabled' => true]);

                    echo $this->Form->control('file_url', ['type' => 'file']);

                    echo $this->Form->control('process_id', ['options' => $processes, 'empty' => true]);
                    echo $this->Form->control('comment_id', ['options' => $comments, 'empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__d('admin', 'Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
