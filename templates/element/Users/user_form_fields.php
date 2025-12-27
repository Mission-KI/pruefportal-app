<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string[]|\Cake\Collection\CollectionInterface $tags
 */

echo $this->Form->control('enabled');
echo $this->Form->control('username', ['type' => 'email']);

if (isset($isEdit) && $isEdit) {
    echo $this->Form->control('set_new_password', [
        'label'     => __('Set New Password'),
        'empty' => true,
        'type' => 'password',
        'pattern' => '^(?=.*[0-9])(?=.*[\.,!@#$%^&*?]).{8,}$',
        'minlength' => 8,
    ]);
} else {
    echo $this->Form->control('password', [
        'pattern' => '^(?=.*[0-9])(?=.*[!@#$%^&*?]).{8,}$',
        'minlength' => 8
    ]);
}

echo $this->Form->control('role');
echo $this->Form->control('key');
echo $this->Form->control('salutation');
echo $this->Form->control('full_name');
echo $this->Form->control('company');
echo $this->Form->control('process_updates');
echo $this->Form->control('comment_notifications');

echo $this->Form->control('tags._ids', ['options' => $tags]);
