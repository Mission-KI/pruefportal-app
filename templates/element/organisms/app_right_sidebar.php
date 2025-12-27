<?php
/**
 * App Right Sidebar Organism
 *
 * The right sidebar container that holds contextual content like actions and navigation
 *
 * @var \App\View\AppView $this
 * @var string $content The content to display in the sidebar (required)
 * @var array $options Additional HTML attributes
 * @var bool $sticky Whether the sidebar should stick to the top when scrolling (default: true)
 */

$content = $content ?? '';
$options = $options ?? [];
$sticky = $sticky ?? true;

$classes = [
    'hidden',        // Hidden on mobile by default
    'lg:block',      // Visible on desktop (≥1200px)
    'w-70',          // Fixed width on desktop
    'p-6 pl-0',
    'overflow-y-auto'
];

if ($sticky) {
    $classes[] = 'lg:fixed';  // Fixed positioning on desktop to break out of flex stacking context
    $classes[] = 'lg:right-0'; // Align to right edge of viewport
    $classes[] = 'lg:top-[calc(var(--layout-header-height)+2.5rem)]'; // Start at same position as main content
    $classes[] = 'lg:h-[calc(100vh-var(--layout-header-height)-2.5rem)]'; // Full height minus header and top padding
    $classes[] = 'lg:z-10';    // Z-index applied at lg breakpoint when fixed
}

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$sidebarOptions = array_merge($options, [
    'class' => implode(' ', $classes),
    'id' => 'app-right-sidebar'
]);
?>

<aside <?= $this->Html->templater()->formatAttributes($sidebarOptions) ?>>
    <?= $content ?>
</aside>
