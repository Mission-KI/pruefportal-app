<?php
/**
 * Badge Atom Component
 *
 * Status indicator badge using design system colors.
 *
 * @var \App\View\AppView $this
 * @var string $text Badge text content
 * @var string $variant Badge style (primary|secondary|success|warning|danger|info)
 * @var string $size Badge size (sm|md|lg)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

// Set defaults
$text = $text ?? '';
$variant = $variant ?? 'primary';
$size = $size ?? 'md';
$options = $options ?? [];
$escape = $escape ?? true;

// Build CSS classes based on variant
$variantClasses = [
    'primary' => 'bg-primary text-white',
    'secondary' => 'bg-gray-100 text-gray-700 border border-gray-300',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800'
];

$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base'
];

$baseClasses = 'inline-flex items-center font-medium rounded-full';
$classes = [
    $baseClasses,
    $variantClasses[$variant] ?? $variantClasses['primary'],
    $sizeClasses[$size] ?? $sizeClasses['md']
];

// Add user-provided classes
if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);

// Prepare content
$content = $escape ? h($text) : $text;
?>

<span<?= $this->Html->templater()->formatAttributes($options) ?>><?= $content ?></span>
