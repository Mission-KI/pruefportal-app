<?php
/**
 * Card Molecule Component
 *
 * Flexible card component that combines atoms (headings, images, buttons) into a reusable molecule.
 * Supports multiple color schemes, flexible content layouts, and customizable actions.
 *
 * @var \App\View\AppView $this
 * @var string $title Card title (required)
 * @var string $subtitle Optional subtitle text
 * @var string $subtitle_position Position of subtitle relative to title (above|below)
 * @var string $body Main content area (accepts HTML)
 * @var array $image Image configuration ['src' => '', 'alt' => '', 'position' => 'top', 'aspect' => 'video']
 * @var array $actions Array of action button configurations
 * @var string $variant Color scheme (default|primary|secondary|success|warning|danger|plain)
 * @var array $options Additional HTML attributes for the card container
 * @var bool $escape Whether to escape HTML content (default: true)
 * @var string $heading_level HTML heading level (h1-h6, default: h3)
 * @var string|false $heading_size Heading size (xs|sm|md|lg|xl|2xl, default: lg). Set to false for native browser styling.
 * @var string|false $heading_weight Heading font weight (normal|medium|semibold|bold, default: semibold). Set to false for native browser styling.
 */

// Set defaults
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$subtitle_position = $subtitle_position ?? 'below';
$body = $body ?? '';
$image = $image ?? [];
$actions = $actions ?? [];
$variant = $variant ?? '';
$options = $options ?? [];
$escape = $escape ?? true;
$heading_level = $heading_level ?? 'h3';
$heading_size = $heading_size ?? 'lg';
$heading_weight = $heading_weight ?? 'semibold';

// Validate required fields
if (empty($title)) {
    return; // Don't render card without title
}

// Build base card classes - minimal styling by default
$cardClasses = [
    'card', // Base card class for styling hooks
    'rounded-lg',
    'shadow-sm',
    'border',
    'overflow-hidden'
];

// Only add variant-specific styling if variant is provided
if ($variant) {
    $cardClasses[] = 'card-' . $variant;
}

// Add user-provided classes
if (isset($options['class'])) {
    $cardClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $cardClasses);

// Prepare image configuration
$hasImage = !empty($image['src']);
$imagePosition = $image['position'] ?? 'top';
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?php if ($hasImage && $imagePosition === 'top'): ?>
        <!-- Card Image (Top) -->
        <div class="card-image">
            <?= $this->element('atoms/image', [
                'src' => $image['src'],
                'alt' => $image['alt'] ?? '',
                'aspect' => $image['aspect'] ?? 'video',
                'fit' => $image['fit'] ?? 'cover',
                'options' => ['class' => 'w-full']
            ]) ?>
        </div>
    <?php endif; ?>

    <?php if ($variant === 'plain'): ?>
        <!-- Plain variant: just render title and body with no styling -->
        <?php if ($title): ?>
            <?= $this->element('atoms/heading', [
                'text' => $title,
                'level' => $heading_level,
                'size' => $heading_size,
                'weight' => $heading_weight,
                'escape' => $escape
            ]) ?>
        <?php endif; ?>

        <?php if ($body): ?>
            <?= $escape ? nl2br(h($body)) : $body ?>
        <?php endif; ?>
    <?php else: ?>
        <!-- Card Header -->
        <div class="px-6 py-4">
            <?php if ($subtitle && $subtitle_position === 'above'): ?>
                <!-- Subtitle Above Title -->
                <div class="card-subtitle card-subtitle-above text-sm mb-1">
                    <?= $escape ? h($subtitle) : $subtitle ?>
                </div>
            <?php endif; ?>

            <!-- Card Title -->
            <?= $this->element('atoms/heading', [
                'text' => $title,
                'level' => $heading_level,
                'size' => $heading_size,
                'weight' => $heading_weight,
                'escape' => $escape
            ]) ?>

            <?php if ($subtitle && $subtitle_position === 'below'): ?>
                <!-- Subtitle Below Title -->
                <div class="card-subtitle card-subtitle-below text-sm mt-1">
                    <?= $escape ? h($subtitle) : $subtitle ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($body): ?>
            <!-- Card Body -->
            <div class="px-6 py-4 card-body">
                <?= $escape ? nl2br(h($body)) : $body ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasImage && $imagePosition === 'body'): ?>
        <!-- Card Image (Body) -->
        <div class="px-6 pb-4 card-image">
            <?= $this->element('atoms/image', [
                'src' => $image['src'],
                'alt' => $image['alt'] ?? '',
                'aspect' => $image['aspect'] ?? 'video',
                'fit' => $image['fit'] ?? 'cover',
                'options' => ['class' => 'w-full rounded-md']
            ]) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($actions)): ?>
        <!-- Card Actions -->
        <div class="px-6 py-3 card-actions">
            <div class="flex flex-wrap gap-2 justify-end">
                <?php foreach ($actions as $action): ?>
                    <?php if (is_array($action)): ?>
                        <?= $this->element('atoms/button', array_merge([
                            'size' => 'SM',
                            'variant' => 'secondary'
                        ], $action)) ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hasImage && $imagePosition === 'bottom'): ?>
        <!-- Card Image (Bottom) -->
        <div class="card-image">
            <?= $this->element('atoms/image', [
                'src' => $image['src'],
                'alt' => $image['alt'] ?? '',
                'aspect' => $image['aspect'] ?? 'video',
                'fit' => $image['fit'] ?? 'cover',
                'options' => ['class' => 'w-full']
            ]) ?>
        </div>
    <?php endif; ?>
</div>
