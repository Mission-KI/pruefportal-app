<?php
/**
 * @var \App\View\AppView $this
 * @var string $logo_url Home URL (optional)
 * @var array $user User data {full_name, role?, initials, profile_url?} (optional)
 * @var string $current_language Current language code (optional)
 * @var array $available_languages Available languages [{code, label}] (optional)
 * @var array|string $logout_url Logout URL (optional)
 * @var bool $show_user_menu Whether to show user menu (default: true)
 * @var array $options Additional HTML attributes
 */

$logo_url = $logo_url ?? '/';
$user = $user ?? null;
$current_language = $current_language ?? 'DE';
$available_languages = $available_languages ?? [];
$logout_url = $logout_url ?? null;
$show_user_menu = $show_user_menu ?? true;
$options = $options ?? [];

$classes = ['flex', 'items-center', 'justify-between'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$classes = array_merge($classes, [
    'bg-white',
    'border-b',
    'border-gray-200',
    'fixed',
    'top-0',
    'left-0',
    'right-0',
    'z-49',
    'w-full',
    'max-w-full',
    'overflow-hidden',
    'h-[var(--layout-header-height)]',
    'py-[var(--layout-header-padding-y)]',
    'px-3',           // Mobile: 12px
    'sm:px-4',        // Small screens: 16px
    'md:px-[var(--layout-header-padding-x)]'  // Desktop: 24px (design token)
]);

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<header <?= $this->Html->templater()->formatAttributes($containerOptions) ?>>
    <div class="flex items-center gap-2 sm:gap-4 min-w-0">
        <!-- Mobile menu toggle button (visible < md breakpoint) -->
        <button type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-1.5 sm:p-2 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition-colors flex-shrink-0"
                aria-label="<?= __('Toggle menu') ?>"
                x-bind:aria-expanded="mobileMenuOpen.toString()">
            <?= $this->element('atoms/icon', [
                'name' => \App\Utility\Icon::DOTS_VERTICAL,
                'size' => 'lg',
                'options' => ['class' => 'text-gray-700']
            ]) ?>
        </button>

        <?= $this->Html->link(
            $this->Html->image('pruefportal_logo2_compact.svg', ['alt' => 'MISSION KI PRÜFPORTAL', 'class' => 'h-6 sm:h-8']),
            $logo_url,
            ['escape' => false, 'class' => 'flex-shrink-0']
        ) ?>
    </div>

    <?php if ($show_user_menu && $user && $logout_url): ?>
        <div class="flex items-center">
            <?= $this->element('organisms/user_menu', [
                'user' => $user,
                'current_language' => $current_language,
                'available_languages' => $available_languages,
                'logout_url' => $logout_url
            ]) ?>
        </div>
    <?php endif; ?>
</header>
