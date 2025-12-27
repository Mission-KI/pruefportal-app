<?php
/**
 * @var array $user
 * @var string $pw
 * @var string $subject
 */
?>
<p><?= __('Hi') ?> <?= h($user['full_name']) ?></p>
<p><?= $subject ?></p>
<p>
    <?= __('You can set the credentials for your account "{0}" after the activation.', $user['username']) ?>
</p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
    <tbody>
    <tr>
        <td align="left">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td> <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'acceptInvitation', 'token' => $user['key']], ['fullBase' => true]) ?>" target="_blank"><?= __('Accept and activate account') ?></a> </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<p><?= __('Good luck! Hope it works.') ?></p>
