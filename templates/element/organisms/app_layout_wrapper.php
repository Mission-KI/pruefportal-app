<?php
/**
 * @var \App\View\AppView $this
 * @var array $breadcrumbs Breadcrumb items [{text, url?, icon?, active?}] (optional)
 * @var string $content Page content (required)
 * @var bool $show_sidebar Whether to show sidebar (default: true)
 * @var bool $show_breadcrumbs Whether to show breadcrumbs (default: true)
 * @var string $container_width Container width: full|lg|md|sm (default: lg)
 * @var string $right_sidebar_content Right sidebar content (optional)
 * @var bool $show_right_sidebar Whether to show right sidebar (default: false)
 * @var bool $show_content_card Whether to wrap content in white card (default: true)
 * @var bool $reserve_sidebar_space Whether to reserve space for right sidebar even when hidden (default: false)
 * @var array $options Additional HTML attributes
 */

$breadcrumbs = $breadcrumbs ?? [];
$content = $content ?? '';
$show_sidebar = $show_sidebar ?? true;
$show_breadcrumbs = $show_breadcrumbs ?? true;
$container_width = $container_width ?? 'lg';
$right_sidebar_content = $right_sidebar_content ?? '';
$show_right_sidebar = $show_right_sidebar ?? false;
$show_content_card = $show_content_card ?? true;
$reserve_sidebar_space = $reserve_sidebar_space ?? false;
$options = $options ?? [];

$maxWidth = match($container_width) {
    'full' => '100%',
    'sm' => 'var(--layout-content-max-width-sm)',
    'md' => 'var(--layout-content-max-width-md)',
    'lg' => 'var(--layout-content-max-width-lg)',
    'xl' => 'var(--layout-content-max-width-xl)',
    default => 'var(--layout-content-max-width-lg)'
};

$classes = [];

// Flexbox fix: Allow flex child to shrink below content width
$classes[] = 'min-w-0';

// When sidebar is shown, content starts after sidebar width on desktop only
if ($show_sidebar) {
    $classes[] = 'lg:ml-[var(--layout-sidebar-width)]';  // Only on desktop
    $classes[] = 'ml-0';  // No margin on mobile/tablet
}

// Account for header height - content should start below header
$classes[] = 'pt-[calc(var(--layout-header-height)+0.75rem)]';    // Mobile: header + 12px
$classes[] = 'sm:pt-[calc(var(--layout-header-height)+.5rem)]';    // Small: header + 16px
$classes[] = 'md:pt-[calc(var(--layout-header-height)+.75rem)]';  // Tablet: header + 24px
$classes[] = 'lg:pt-[calc(var(--layout-header-height)+1rem)]';  // Desktop: header + 40px

// Min height should be full viewport minus header
$classes[] = 'min-h-[calc(100vh-var(--layout-header-height))]';

// Responsive padding for content area (left, right, bottom only)
$classes[] = 'px-3 pb-3';         // Mobile: 0.75rem (12px)
$classes[] = 'sm:px-4 sm:pb-4';   // Small: 1rem (16px)
$classes[] = 'md:px-6 md:pb-6';   // Tablet: 1.5rem (24px)
$classes[] = 'lg:px-6 lg:pb-10'; // Desktop: 2.5rem (40px)

// Reserve space for right sidebar when needed (e.g., form pages with tooltips)
if ($reserve_sidebar_space) {
    $classes[] = 'lg:mr-[calc(var(--spacing)*70+1.5rem)]'; // Right sidebar width (w-70) + padding
}

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<div class="flex">
    <!-- Main content area -->
    <div id="layout-main-container" <?= $this->Html->templater()->formatAttributes($containerOptions) ?>>
        <?php if ($show_breadcrumbs && !empty($breadcrumbs)): ?>
            <?= $this->element('molecules/breadcrumb_nav', [
                'items' => $breadcrumbs
            ]) ?>
        <?php endif; ?>

        <div id="layout-content-wrapper" class="w-full overflow-visible">
            <?php if ($show_content_card): ?>
                <div id="layout-content-card" class="p-3 sm:p-4 md:p-6 lg:p-10 bg-white rounded-[var(--radius-lg)] shadow-[var(--shadow-card-md)]">
                    <?= $content ?>
                </div>
            <?php else: ?>
                <?= $content ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right sidebar -->
    <?php if ($show_right_sidebar && !empty($right_sidebar_content)): ?>
        <?= $this->element('organisms/app_right_sidebar', [
            'content' => $right_sidebar_content
        ]) ?>
    <?php endif; ?>
</div>
