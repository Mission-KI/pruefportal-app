<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Upload $upload
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Form->postLink(__d('admin', 'Download Upload'), ['action' => 'download', $upload->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Edit Upload'), ['action' => 'edit', $upload->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__d('admin', 'Delete Upload'), ['action' => 'delete', $upload->id], ['confirm' => __d('admin', 'Are you sure you want to delete {0}?', $upload->name), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Uploads'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Add Upload'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="uploads view content">
            <h3><?= h($upload->name) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($upload->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Key') ?></th>
                    <td><?= h($upload->key) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Name') ?></th>
                    <td><?= h($upload->name) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Location') ?></th>
                    <td><?= h($upload->location) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Etag') ?></th>
                    <td><?= h($upload->etag) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Size') ?></th>
                    <td><?= h($upload->size) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process') ?></th>
                    <td><?= $upload->hasValue('process') ? $this->Html->link($upload->process->title, ['controller' => 'Processes', 'action' => 'view', $upload->process->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Indicator') ?></th>
                    <td><?= $upload->hasValue('indicator') ? $this->Html->link($upload->indicator->title, ['controller' => 'Indicators', 'action' => 'view', $upload->indicator->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Comment') ?></th>
                    <td><?= ($upload->comment_id) ? $this->Html->link($upload->comment_id . '. ' . __d('admin', 'Comment'), ['controller' => 'Comments', 'action' => 'view', $upload->comment_id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($upload->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($upload->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
