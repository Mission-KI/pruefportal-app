<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Log in'));

// Build form content
ob_start();
?>

<?= $this->Form->create(null, [
    'class' => 'space-y-6',
    'x-data' => '{ formValid: false }',
    'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
    '@input' => 'formValid = $el.checkValidity()',
    '@change' => 'formValid = $el.checkValidity()'
]) ?>

<?= $this->FormField->control('username', [
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

<?= $this->FormField->control('password', [
    'label' => __('Password'),
    'required' => true,
    'placeholder' => __('Text'),
    'class' => 'w-full'
]) ?>

<div class="mt-6">
    <?= $this->element('atoms/button', [
        'label' => __('Log in'),
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

<div class="mt-4 flex justify-between items-center flex-wrap">
    <p class="text-gray-600 text-sm">
        <?= __('Forgot password?') ?>
        <?= $this->Html->link(
            __('Reset here'),
            ['controller' => 'Users', 'action' => 'resetPassword'],
            ['class' => 'text-brand-deep font-semibold underline']
        ) ?>
    </p>

    <p class="text-gray-600 text-sm">
        <?= __('No account yet?') ?>
        <?= $this->Html->link(
            __('Create one now'),
            ['controller' => 'Users', 'action' => 'register'],
            ['class' => 'text-brand-deep font-semibold  underline']
        ) ?>
    </p>
</div>

<?php
$formContent = ob_get_clean();

// Render using shared auth layout
echo $this->element('organisms/app_auth_layout', [
    'title' => __('Log in'),
    'subtitle' => __('Please enter your credentials to continue'),
    'content' => $formContent,
    'show_footer' => true,
    'show_docs_button' => true,
    'allow_scroll' => false
]);
?>
