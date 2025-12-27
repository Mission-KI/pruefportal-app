<?php
/**
 * @var \App\View\AppView $this
 * @var array $user User data {full_name, role?, initials, profile_url?} (required)
 * @var App\Controller\AppController $currentLanguage Current language code (required)
 * @var App\Controller\AppController $availableLanguages Available languages [{code, label}] (required)
 * @var array|string $logout_url Logout URL (required)
 * @var array $options Additional HTML attributes
 */

$user = $user ?? [];
$currentLanguage = $currentLanguage ?? 'de';
$availableLanguages = $availableLanguages ?? [];
$logout_url = $logout_url ?? '';
$options = $options ?? [];
echo $this->Html->scriptBlock('function changeLanguage(elem) {
    window.location.href = "' . $this->Url->build(['controller' => 'Users', 'action' => 'changeLanguage']) . '?lang=" + elem.value;
}', ['block' => true]);

if (empty($user) || empty($logout_url)) {
    return;
}

$classes = ['flex', 'items-center', 'gap-1.5', 'sm:gap-3', 'md:gap-4'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<div <?= $this->Html->templater()->formatAttributes($containerOptions) ?>>
    <div class="hidden sm:flex items-center">
        <?= $this->element('molecules/user_badge', [
            'avatar_initials' => $user['initials'] ?? '',
            'full_name' => $user['full_name'] ?? '',
            'role' => $user['role'] ?? null,
            'url' => $user['profile_url'] ?? null
        ]) ?>
    </div>

    <?= $this->element('atoms/button', [
        'label' => __('Logout'),
        'icon' => 'log-out-01',
        'variant' => 'secondary',
        'size' => 'sm',
        'url' => $logout_url,
        'options' => ['class' => 'flex-shrink-0']
    ]) ?>

    <?php if (!empty($availableLanguages)): ?>
        <!-- <div class="hidden md:block">
            <?= $this->element('atoms/form_select', [
                'name' => 'language',
                'id' => 'user-menu-language',
                'options' => $availableLanguages,
                'value' => $currentLanguage,
                'attributes' => ['class' => 'min-w-12', 'onchange' => 'changeLanguage(this)']
            ]) ?>
        </div> -->
    <?php endif; ?>
</div>
