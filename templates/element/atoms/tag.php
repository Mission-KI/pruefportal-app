<?php
/**
 * Tag Atom Component
 *
 * Text label for categories, reference IDs, and status indicators.
 * Distinct from badge (notification counts).
 *
 * @var \App\View\AppView $this
 * @var string $text Tag text content
 * @var string $variant Color variant (default|primary|secondary|info|success|warning|danger)
 * @var string $size Tag size (sm|md|lg)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

$text = $text ?? '';
$variant = $variant ?? 'default';
$size = $size ?? 'md';
$options = $options ?? [];
$escape = $escape ?? true;

$variantClasses = [
    'default' => 'bg-gray-100 text-gray-700 border border-gray-300',
    'primary' => 'bg-brand-lightest text-brand-deep border border-brand-light',
    'secondary' => 'bg-gray-50 text-gray-600 border border-gray-200',
    'info' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'success' => 'bg-success-50 text-success-700 border border-success-200',
    'warning' => 'bg-warning-50 text-warning-700 border border-warning-200',
    'danger' => 'bg-error-50 text-error-700 border border-error-200'
];

$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base'
];

$baseClasses = 'inline-flex items-center font-medium rounded';
$classes = [
    $baseClasses,
    $variantClasses[$variant] ?? $variantClasses['default'],
    $sizeClasses[$size] ?? $sizeClasses['md']
];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);

$content = $escape ? h($text) : $text;
?>

<span<?= $this->Html->templater()->formatAttributes($options) ?>><?= $content ?></span>
