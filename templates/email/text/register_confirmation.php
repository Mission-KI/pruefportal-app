<?php
/**
 * @var array $user
 */
?>
<?= __('Hi') ?> <?= h($user['salutation']) ?>

<?= __('Please open the link below to activate your account.') ?>

<?= $this->Url->build(['controller' => 'Users', 'action' => 'activate', 'token' => $user['key'], 'isInvite' => false], ['fullBase' => true]) ?>

<?= __('Good luck! Hope it works.') ?>
