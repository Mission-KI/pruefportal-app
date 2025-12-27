<?php
/**
 * Image Atom Component
 *
 * Reusable image element with responsive behavior, lazy loading, and aspect ratio support.
 *
 * @var \App\View\AppView $this
 * @var string $src Image source URL (required)
 * @var string $alt Alt text for accessibility (required)
 * @var string $aspect Aspect ratio (square|video|wide|tall|auto)
 * @var string $fit Object fit behavior (cover|contain|fill|scale-down|none)
 * @var bool $lazy Enable lazy loading (default: true)
 * @var array $options Additional HTML attributes
 */

// Set defaults
$src = $src ?? '';
$alt = $alt ?? '';
$aspect = $aspect ?? 'auto';
$fit = $fit ?? 'cover';
$lazy = $lazy ?? true;
$options = $options ?? [];

// Aspect ratio class mappings
$aspectClasses = [
    'square' => 'aspect-square',
    'video' => 'aspect-video',
    'wide' => 'aspect-[3/2]',
    'tall' => 'aspect-[2/3]',
    'auto' => ''
];

// Object fit class mappings
$fitClasses = [
    'cover' => 'object-cover',
    'contain' => 'object-contain',
    'fill' => 'object-fill',
    'scale-down' => 'object-scale-down',
    'none' => 'object-none'
];

// Build CSS classes
$classes = [
    'block', // Ensure proper display
    'w-full', // Full width by default
    $fitClasses[$fit] ?? $fitClasses['cover']
];

// Add aspect ratio if specified
if ($aspect !== 'auto' && isset($aspectClasses[$aspect])) {
    $classes[] = $aspectClasses[$aspect];
}

// Add user-provided classes
if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', array_filter($classes));

// Set required attributes
$options['src'] = $src;
$options['alt'] = $alt;

// Add lazy loading if enabled
if ($lazy) {
    $options['loading'] = 'lazy';
}

// Validate required fields
if (empty($src)) {
    return; // Don't render if no source
}
if (empty($alt)) {
    $options['alt'] = ''; // Empty alt for decorative images
}
?>

<img<?= $this->Html->templater()->formatAttributes($options) ?>>
