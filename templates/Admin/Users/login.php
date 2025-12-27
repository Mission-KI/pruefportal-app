<?php $this->assign('title', __d('admin', 'Log in')); ?>
<div class="users form content">
    <?= $this->Form->create(null, [
        'url' => [
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'login',
        ],
    ]) ?>
    <fieldset>
        <legend><?= __d('admin', 'Log in') ?></legend>
        <div class="form-group">
        <?php
            echo $this->Form->control('username', ['type' => 'email', 'label' => __d('admin', 'Username'), 'class' => 'required form-control', 'required' => true]);
        ?>
        </div>
        <div class="form-group">
        <?php
            echo $this->Form->control('password', ['label' => __d('admin', 'Password'), 'class' => 'required form-control', 'required' => true]);
        ?>
        </div>
    </fieldset>
    <?= $this->element('atoms/button', [
        'variant' => 'primary',
        'size' => 'md',
        'label' => __d('admin', 'Log in'),
        'options' => ['type' => 'submit']
    ]) ?>
    <?= $this->Form->end() ?>
</div>
