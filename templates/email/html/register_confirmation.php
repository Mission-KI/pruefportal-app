<?php
/**
 * @var array $user
 */
?>
<p><?= __('Hi') ?> <?= h($user['salutation']) ?></p>
<p><?= __('Please open the link below to activate your account.') ?></p>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
    <tbody>
    <tr>
        <td align="left">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td> <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'activate', 'token' => $user['key']], ['fullBase' => true]) ?>" target="_blank"><?= __('Activate account') ?></a> </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<p><?= __('Good luck! Hope it works.') ?></p>
