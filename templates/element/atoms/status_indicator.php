<?php
/**
 * Status Indicator Atom Component
 *
 * Displays a colored dot/icon to indicate process status.
 * Used in process cards to provide visual status feedback.
 *
 * @var \App\View\AppView $this
 * @var int $status_id Process status ID (required)
 * @var string $size Size variant: sm|md|lg (default: md)
 * @var bool $show_icon Whether to show icon inside the indicator (default: false)
 * @var array $options Additional HTML attributes
 *
 * Status ID mapping (simplified 3-state):
 * 0      => Inactive (gray)
 * 1-59   => In Progress (blue)
 * 60+    => Complete (green)
 */

$status_id = $status_id ?? 0;
$size = $size ?? 'md';
$show_icon = $show_icon ?? false;
$options = $options ?? [];

if ($status_id == 0) {
    $colorClass = 'bg-gray-400';
} elseif ($status_id >= 60) {
    $colorClass = 'bg-green-500';
} else {
    $colorClass = 'bg-brand-light';
}

$sizeClasses = [
    'sm' => 'w-2 h-2',
    'md' => 'w-3 h-3',
    'lg' => 'w-4 h-4'
];
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];

$classes = [
    'inline-block',
    'rounded-full',
    'flex-shrink-0',
    $colorClass,
    $sizeClass
];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
$options['aria-hidden'] = 'true';
?>

<span<?= $this->Html->templater()->formatAttributes($options) ?>></span>
