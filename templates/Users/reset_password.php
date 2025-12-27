<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Form\ResetPasswordForm $resetPassword
 */
$this->assign('title', __('Reset Password'));

// Build form content
ob_start();
?>

<?= $this->Form->create($resetPassword, [
    'class' => 'space-y-6',
    'x-data' => '{ formValid: false }',
    'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
    '@input' => 'formValid = $el.checkValidity()',
    '@change' => 'formValid = $el.checkValidity()'
]) ?>

<?= $this->FormField->control('reset_email', [
    'type' => 'email',
    'label' => __('Email Address'),
    'required' => true,
    'placeholder' => __('name@domain.com'),
    'icon' => 'mail',
    'class' => 'w-full',
    'error_messages' => [
        __('Please enter a valid email address')
    ]
]) ?>

<div class="mt-6">
    <?= $this->element('atoms/button', [
        'label' => __('Reset Password'),
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
        <?= __('Remember your password?') ?>
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
    'title' => __('Forgot your password?'),
    'subtitle' => __('Please enter your email address. You will receive an email with instructions to reset your password.'),
    'content' => $formContent,
    'show_footer' => true,
    'show_docs_button' => false,
    'allow_scroll' => false
]);
?>
