<?php
/**
 * Status Icon Atom
 *
 * Displays an icon with optional text and conditional styling based on status
 *
 * @var \App\View\AppView $this
 * @var string $icon - Bootstrap icon name (without 'bi-' prefix)
 * @var string|null $text - Optional text to display after icon
 * @var bool $status - Status state (true = success/green, false = danger/red)
 * @var string|null $title - Optional tooltip text
 * @var string|null $class - Additional CSS classes
 */

// Set default values
$icon = $icon ?? 'circle';
$text = $text ?? null;
$status = $status ?? true;
$title = $title ?? null;
$class = $class ?? '';

// Determine color classes based on status
$colorClass = $status ? 'text-green-600' : 'text-red-600';

// Build icon name
$iconName = $status ? "{$icon}-circle" : "dash-{$icon}";

// Combine classes
$classes = "inline-flex items-center gap-1 {$colorClass} {$class}";

// Build title attribute
$titleAttr = $title ? " title=\"{$title}\"" : '';
?>
<span class="<?= $classes ?>"<?= $titleAttr ?>>
    <?= $this->element('atoms/icon', [
        'name' => $iconName,
        'size' => 'sm',
        'options' => ['class' => $colorClass]
    ]) ?>
    <?php if ($text): ?>
        <span><?= h($text) ?></span>
    <?php endif; ?>
</span>