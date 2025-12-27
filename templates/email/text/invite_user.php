<?php
/**
 * @var array $user
 * @var string $pw
 * @var string $subject
 */
?>
<?= __('Hi') ?> <?= h($user['salutation']) ?>

<?= $subject ?>

<?= __('You can set the credentials for your account "{0}" after the activation.', $user['username']) ?>

<?= $this->Url->build(['controller' => 'Users', 'action' => 'acceptInvitation', 'token' => $user['key']], ['fullBase' => true]) ?>


<?= __('Good luck! Hope it works.') ?>
