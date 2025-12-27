<?php
/**
 * Table Cell Atom
 *
 * Basic table cell wrapper with consistent styling.
 *
 * @var \App\View\AppView $this
 * @var string $content Cell content (required)
 * @var string $align Text alignment (default: "left")
 * @var bool $truncate Enable text truncation (default: false)
 * @var bool $nowrap Prevent text wrapping (default: false)
 * @var array $options Additional HTML attributes
 */

$content = $content ?? '';
$align = $align ?? 'left';
$truncate = $truncate ?? false;
$nowrap = $nowrap ?? false;
$options = $options ?? [];

$defaultClasses = 'px-6 py-4 text-sm text-gray-900';
$alignClasses = [
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right',
];

$classes = $defaultClasses . ' ' . ($alignClasses[$align] ?? $alignClasses['left']);

if ($nowrap) {
    $classes .= ' whitespace-nowrap';
}

if ($truncate) {
    $classes .= ' truncate';
}

if (isset($options['class'])) {
    $classes .= ' ' . $options['class'];
    unset($options['class']);
}

$attributes = array_merge([
    'class' => $classes,
], $options);
?>

<td<?= $this->Html->templater()->formatAttributes($attributes) ?>>
    <?= $content ?>
</td>
