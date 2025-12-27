<?php
/**
 * @var array $user
 */
?>
<?= __('Hi') ?> <?= $user['salutation'] ?>

<?= __('Please open the link below to set a new password.') ?>

<?= $this->Url->build(['controller' => 'Users', 'action' => 'setNewPassword', 'token' => $user['token']], ['fullBase' => true]) ?>

<?= __('This link will expire in 10 minutes.') ?>

<?= __('Good luck! Hope it works.') ?>
