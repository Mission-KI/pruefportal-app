<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Mailer\Mailer;

class UserMailer extends Mailer
{
    public function __construct()
    {
        parent::__construct();

        // Set default sender type for all emails from this mailer
        $this->addHeaders(['X-Sender-Type' => 'default']);
    }

    /**
     * Reset user password
     *
     * @param $user
     * @return void
     */
    public function resetPassword($user): void
    {
        $this->setEmailFormat('both')
            ->setSubject(__('MISSION KI') . ' - ' . __('Reset password Subject'))
            ->setTo([$user['email'] => $user['full_name']])
            ->viewBuilder()
            ->setTemplate('resetPassword')
            ->setVars(['user' => $user]);
    }

    /**
     * Send register confirmation
     *
     * @param \App\Model\Entity\User $user
     * @return void
     */
    public function registerConfirmation(User $user): void
    {
        $userData = $user->toArray();
        $userData['key'] = $user->key;
        $this->setEmailFormat('both')
            ->setSubject(__('MISSION KI') . ' - ' . __('Register confirmation Subject'))
            ->setTo([$userData['username'] => $userData['full_name']])
            ->addHeaders(['X-Sender-Type' => 'noreply'])
            ->viewBuilder()
            ->setTemplate('registerConfirmation')
            ->setVars(['user' => $userData]);
    }

    /**
     * Invite User to a Process
     *
     * @param \App\Model\Entity\User $user
     * @param string $subject
     * @return void
     */
    public function inviteUser(User $user, string $subject): void
    {
        $userData = $user->toArray();
        $userData['key'] = $user->key;
        $this->setEmailFormat('both')
            ->setSubject(__('MISSION KI') . ' - ' . __('Invite User to a Process Subject'))
            ->setTo([$userData['username'] => $userData['full_name']])
            ->viewBuilder()
            ->setTemplate('inviteUser')
            ->setVars(['user' => $userData, 'subject' => $subject]);
    }
}
