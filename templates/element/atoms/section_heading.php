<?php
/**
 * @var \App\View\AppView $this
 * @var string $text Section heading text (required)
 * @var string $level Heading level: h1|h2|h3|h4|h5|h6 (default: h6)
 * @var string $variant Variant: sidebar|content (default: content)
 * @var array $options Additional HTML attributes
 */

$text = $text ?? '';
$level = $level ?? 'h6';
$variant = $variant ?? 'content';
$options = $options ?? [];

if (empty($text)) {
    return;
}

$classes = [];
$styles = [];

if ($variant === 'sidebar') {
    $styles[] = 'font-size: var(--font-size-text-xs)';
    $styles[] = 'color: var(--color-gray-500)';
    $styles[] = 'letter-spacing: 0.05em';
    $styles[] = 'font-weight: var(--font-weight-semibold)';
    $styles[] = 'padding: var(--nav-section-heading-padding)';
}

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

if (!empty($classes)) {
    $options['class'] = implode(' ', $classes);
}

if (!empty($styles)) {
    $options['style'] = ($options['style'] ?? '') . implode('; ', $styles);
}

$tag = in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) ? $level : 'h6';
?>

<<?= $tag ?><?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= h($text) ?>
</<?= $tag ?>>
