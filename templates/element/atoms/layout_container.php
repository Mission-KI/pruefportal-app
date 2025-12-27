<?php
/**
 * Layout Container Atom
 *
 * Centered responsive container following the `row justify-content-center` + `col-md-8 col-lg-6` pattern
 *
 * @var \App\View\AppView $this
 * @var string $content - Content to display inside the container
 * @var string $width - Width variant: 'sm', 'md', 'lg', 'xl' (default: 'md')
 * @var string|null $class - Additional CSS classes for outer container
 * @var string|null $innerClass - Additional CSS classes for inner container
 */

// Set default values
$content = $content ?? '';
$width = $width ?? 'md';
$class = $class ?? '';
$innerClass = $innerClass ?? '';

// Define width classes based on variant
$widthClasses = [
    'sm' => 'w-full max-w-sm mx-auto',
    'md' => 'w-full max-w-2xl mx-auto', // Equivalent to col-md-8 col-lg-6
    'lg' => 'w-full max-w-4xl mx-auto',
    'xl' => 'w-full max-w-6xl mx-auto'
];

$containerWidth = $widthClasses[$width] ?? $widthClasses['md'];

// Combine classes
$outerClasses = "flex justify-center {$class}";
$innerClasses = "{$containerWidth} {$innerClass}";
?>
<div class="<?= $outerClasses ?>">
    <div class="<?= $innerClasses ?>">
        <?= $content ?>
    </div>
</div>