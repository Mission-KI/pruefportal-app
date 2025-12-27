<?php
/**
 * Primary Card Molecule
 *
 * A specialized card component with consistent primary styling used for
 * section headers and introductory content throughout the application.
 * This is a convenience wrapper around the card molecule with preset values.
 *
 * @var \App\View\AppView $this
 * @var string $title Card title (required)
 * @var string $subtitle Card subtitle (optional)
 * @var string $body Card body content (optional)
 * @var string $icon Icon name to display (optional)
 * @var bool $escape Whether to escape HTML content (default: true)
 * @var array $options Additional HTML attributes for the card container
 */

$title = $title ?? '';
$subtitle = $subtitle ?? '';
$body = $body ?? '';
$icon = $icon ?? '';
$escape = $escape ?? true;
$options = $options ?? [];

// Add default margin bottom if no class is specified
if (!isset($options['class'])) {
    $options['class'] = 'mb-6 hyphens-auto break-words';
}

echo $this->element('molecules/card', [
    'variant' => 'primary',
    'title' => $title,
    'subtitle' => $subtitle,
    'subtitle_position' => 'above',
    'body' => $body,
    'icon' => $icon,
    'escape' => $escape,
    'heading_level' => 'h4',
    'heading_size' => false,
    'heading_weight' => false,
    'options' => $options
]);
