<?php
/**
 * Heading Atom Component
 *
 * Reusable heading element with consistent typography and spacing.
 * Supports semantic HTML levels and visual sizing variants.
 *
 * @var \App\View\AppView $this
 * @var string $text Heading text content
 * @var string $level HTML heading level (h1|h2|h3|h4|h5|h6)
 * @var string|false $size Visual size variant (xs|sm|md|lg|xl|2xl). Set to false for native browser styling.
 * @var string|false $weight Font weight (normal|medium|semibold|bold). Set to false for native browser styling.
 * @var string $color Text color classes (optional, inherits by default)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

// Set defaults
$text = $text ?? '';
$level = $level ?? 'h2';
$color = $color ?? ''; // Default to inherit
$options = $options ?? [];
$escape = $escape ?? true;

// Handle size and weight with defaults
// Use false as sentinel value to indicate "use native browser styling"
$size = $size ?? 'md';
$weight = $weight ?? 'semibold';

// Size class mappings
$sizeClasses = [
    'xs' => 'text-xs',
    'sm' => 'text-sm',
    'md' => 'text-md',
    'lg' => 'text-lg',
    'xl' => 'text-xl',
    '2xl' => 'text-2xl'
];

// Weight class mappings
$weightClasses = [
    'normal' => 'font-normal',
    'regular' => 'font-regular',
    'medium' => 'font-medium',
    'semibold' => 'font-semibold',
    'bold' => 'font-bold'
];

// Build CSS classes
$classes = [];

// Only add size and weight classes if they're not false (false = use native browser styling)
if ($size !== false) {
    $classes[] = $sizeClasses[$size] ?? $sizeClasses['md'];
}
if ($weight !== false) {
    $classes[] = $weightClasses[$weight] ?? $weightClasses['semibold'];
}

// Only add color if explicitly provided
if ($color) {
    $classes[] = $color;
}

// Add user-provided classes
if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);

// Prepare content
$content = $escape ? h($text) : $text;

// Validate heading level
$validLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
if (!in_array($level, $validLevels)) {
    $level = 'h2';
}
?>

<<?= $level ?><?= $this->Html->templater()->formatAttributes($options) ?>><?= $content ?></<?= $level ?>>
