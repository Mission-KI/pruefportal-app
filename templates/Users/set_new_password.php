<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', __('Set New Password'));

// Build form content
ob_start();
?>

<?= $this->Form->create($user, [
    'class' => 'space-y-6',
    'x-data' => '{ formValid: false }',
    'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
    '@input' => 'formValid = $el.checkValidity()',
    '@change' => 'formValid = $el.checkValidity()'
]) ?>

<?= $this->FormField->control('set_new_password', [
    'type' => 'password',
    'label' => __('New Password'),
    'required' => true,
    'placeholder' => __('Enter your new password'),
    'class' => 'w-full',
    'pattern' => '.{8,}',
    'minlength' => 8,
    'error_messages' => [
        __('Password must be at least 8 characters and include at least one number and one special character')
    ]
]) ?>
<p class="text-sm text-gray-500 mt-1">
    <?= __('Use 8 or more characters with a mix of letters, numbers & symbols') ?>
</p>

<div class="mt-6">
    <?= $this->element('atoms/button', [
        'label' => __('Update Password'),
        'variant' => 'primary',
        'size' => 'MD',
        'type' => 'submit',
        'options' => [
            'class' => 'w-full',
            'x-bind:disabled' => '!formValid'
        ]
    ]) ?>
</div>

<?= $this->Form->end() ?>

<div class="mt-4 text-center">
    <p class="text-gray-600 text-sm">
        <?= $this->Html->link(
            __('Back to Login'),
            ['controller' => 'Users', 'action' => 'login'],
            ['class' => 'text-brand-deep font-semibold underline']
        ) ?>
    </p>
</div>

<?php
$formContent = ob_get_clean();

// Render using shared auth layout
echo $this->element('organisms/app_auth_layout', [
    'title' => __('Set New Password'),
    'subtitle' => __('Please enter a new password for your account: {0}', h($user->username)),
    'content' => $formContent,
    'show_footer' => false,
    'show_docs_button' => false,
    'allow_scroll' => false
]);
?>
