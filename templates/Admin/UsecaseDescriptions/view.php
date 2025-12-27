<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UsecaseDescription $usecaseDescription
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'Edit Usecase Description'), ['action' => 'edit', $usecaseDescription->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Usecase Descriptions'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="usecaseDescriptions view content">
            <h3><?= __d('admin', 'Usecase Description') ?> <?= h($usecaseDescription->id) ?></h3>
            <table>
                <tr>
                    <th><?= __d('admin', 'Id') ?></th>
                    <td><?= $this->Number->format($usecaseDescription->id) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Process') ?></th>
                    <td><?= $usecaseDescription->hasValue('process') ? $this->Html->link($usecaseDescription->process->title, ['controller' => 'Processes', 'action' => 'view', $usecaseDescription->process->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'User') ?></th>
                    <td><?= $usecaseDescription->hasValue('user') ? $this->Html->link($usecaseDescription->user->username, ['controller' => 'Users', 'action' => 'view', $usecaseDescription->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Step') ?></th>
                    <td><?= $this->Number->format($usecaseDescription->step) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Version') ?></th>
                    <td><?= $this->Number->format($usecaseDescription->version) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Created') ?></th>
                    <td><?= h($usecaseDescription->created) ?></td>
                </tr>
                <tr>
                    <th><?= __d('admin', 'Modified') ?></th>
                    <td><?= h($usecaseDescription->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __d('admin', 'Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($usecaseDescription->description)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
