<?php
/**
 * @var \App\View\AppView $this
 * @var array $sections Navigation sections [{heading?, items: [{text, url, icon, active?, external?}]}] (required)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$sections = $sections ?? [];
$options = $options ?? [];

if (empty($sections)) {
    return;
}

$classes = [
    'bg-gray-50',
    'w-[var(--layout-sidebar-width)]',
    'fixed',
    'left-0',
    'top-[var(--layout-header-height)]',
    'h-[calc(100vh-var(--layout-header-height))]',
    'overflow-y-auto',
    'p-[var(--layout-sidebar-padding)]',
    'border-r',
    'border-gray-200',
    'z-50',
    'transition-transform',
    'duration-300',
    'ease-in-out',
    // Desktop: always visible (≥992px)
    'md:translate-x-0',
    // Mobile & Tablet: hidden by default, slides in when menu open
    '-translate-x-full'
];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

if (isset($options['style'])) {
    unset($options['style']);
}

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<aside <?= $this->Html->templater()->formatAttributes($containerOptions) ?>
       aria-label="<?= __('Main navigation') ?>"
       x-bind:class="{ '-translate-x-full': !mobileMenuOpen, 'translate-x-0': mobileMenuOpen }">
    <nav class="space-y-4">
        <?php foreach ($sections as $index => $section): ?>
            <?= $this->element('molecules/sidebar_nav_section', [
                'heading' => $section['heading'] ?? null,
                'items' => $section['items'] ?? [],
                'show_divider' => $index > 0 && isset($section['heading'])
            ]) ?>
        <?php endforeach; ?>
    </nav>
</aside>
