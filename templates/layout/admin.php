<?php
use Cake\Core\Configure;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="generator" content="periscope.de CMS <?php echo Configure::version(); ?> - Copyright <?php echo date('Y'); ?>">

    <title><?php echo strip_tags($this->fetch('title')); ?> - <?php echo __d('admin', 'Mission KI Administration'); ?></title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'admin']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <?php echo $this->Html->link('<span>MISSION</span>KI <span>Administration</span>', ['controller' => 'Users', 'action' => 'display'], ['escape' => false]); ?>
        </div>
    <?php
        if($this->Identity->isLoggedIn()) {
    ?>
        <input type="checkbox" id="check">
        <label for="check" class="checkbtn">
            <i class="fas fa-bars"></i>
        </label>
        <div class="nav-mobile w3-animate-right">
            <?= $this->Html->link(__d('admin', 'List Comments'), ['controller' => 'Comments', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Criteria'), ['controller' => 'Criteria', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Indicators'), ['controller' => 'Indicators', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Notifications'), ['controller' => 'Notifications', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Processes'), ['controller' => 'Processes', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Projects'), ['controller' => 'Projects', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Tags'), ['controller' => 'Tags', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Uploads'), ['controller' => 'Uploads', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Usecase Descriptions'), ['controller' => 'UsecaseDescriptions', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'List Users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__d('admin', 'Log out'), ['prefix' => false, 'controller' => 'Users', 'action' => 'logout']) ?>
        </div>
    <?php
        }
    ?>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
        <?php echo $this->Html->image('periscope-power.svg', ['alt' => 'Periscope Power', 'url' => 'https://www.periscope.de/']); ?>
    </footer>
</body>
</html>
