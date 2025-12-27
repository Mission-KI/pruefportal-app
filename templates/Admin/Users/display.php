<?php
/**
 * @var \App\Model\Entity\User $user
 * @var \App\Controller\AppController $statuses
 */
    $this->assign('title', __d('admin', 'Welcome to the Administration'));
    $admin_tree_cell = $this->cell('Projects::admin_tree', ['statuses' => $statuses]);
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Modules') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Criteria'), ['controller' => 'Criteria', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Comments'), ['controller' => 'Comments', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Indicators'), ['controller' => 'Indicators', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Notifications'), ['controller' => 'Notifications', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Processes'), ['controller' => 'Processes', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Projects'), ['controller' => 'Projects', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Tags'), ['controller' => 'Tags', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Uploads'), ['controller' => 'Uploads', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Usecase Descriptions'), ['controller' => 'UsecaseDescriptions', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="content">
            <h2><?= __d('admin', 'Welcome to the Administration'); ?></h2>
            <p><?= __d('admin', 'You are logged in as'); ?>: <strong><?= $user->username ?></strong> (<?= $user->role->name ?>)</p>
            <!-- templates/cell/Moderation/display.php -->
            <?php //= $cell ?>

            <!-- templates/cell/Projects/admin_tree.php -->
            <?= $admin_tree_cell ?>

        </div>
    </div>
</div>
