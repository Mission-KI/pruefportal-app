<?php
/**
 * Icon Atom Component
 *
 * Renders SVG icons from the webroot/icons directory with customizable size and color.
 * Uses inline SVG for maximum flexibility with CSS styling and animations.
 *
 * @var \App\View\AppView $this
 * @var string|\App\Utility\Icon $name Icon filename without .svg extension or Icon enum (required)
 * @var string $size Icon size (xs|sm|md|lg|xl|2xl) - default: md
 * @var array $options Additional HTML attributes for the icon wrapper
 * @var bool $spin Add spinning animation (default: false)
 * @var bool $pulse Add pulse animation (default: false)
 * @var bool $bounce Add bounce animation (default: false)
 */

use Cake\Core\Configure;
use App\Utility\Icon;

// Set defaults
$name = $name ?? null;
$size = $size ?? 'md';
$options = $options ?? [];
$spin = $spin ?? false;
$pulse = $pulse ?? false;
$bounce = $bounce ?? false;

// Validate required parameters
if (!$name) {
    if (Configure::read('debug')) {
        echo '<span class="text-red-500">[Icon: name required]</span>';
    }
    return;
}

// Handle Icon enum - extract the filename value
if ($name instanceof Icon) {
    $name = $name->value;
}

// Build CSS classes
$classes = ['icon', 'icon-' . $size];

// Add animation classes
if ($spin) {
    $classes[] = 'icon-spin';
}
if ($pulse) {
    $classes[] = 'icon-pulse';
}
if ($bounce) {
    $classes[] = 'icon-bounce';
}

// Add user-provided classes
if (isset($options['class'])) {
    if (is_array($options['class'])) {
        $classes = array_merge($classes, $options['class']);
    } else {
        $classes[] = $options['class'];
    }
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);

// Get SVG content using the Icon helper
$svgContent = '';
if ($this->helpers()->has('Icon')) {
    $svgContent = $this->Icon->getSvg($name);
} else {
    // Fallback to direct file reading if helper not available
    $iconPath = WWW_ROOT . 'icons' . DS . $name . '.svg';
    if (file_exists($iconPath)) {
        $svgContent = file_get_contents($iconPath);
        // Process SVG to use currentColor
        $svgContent = str_replace(
            [
                'stroke="black"', 'stroke="#000000"', 'stroke="#000"',
                'fill="black"', 'fill="#000000"', 'fill="#000"',
                'fill="#3C0483"',  // Replace hardcoded purple fill from quality dimension icons
                'stroke="#3C0483"'  // Replace hardcoded purple stroke from quality dimension icons
            ],
            [
                'stroke="currentColor"', 'stroke="currentColor"', 'stroke="currentColor"',
                'fill="currentColor"', 'fill="currentColor"', 'fill="currentColor"',
                'fill="currentColor"',
                'stroke="currentColor"'
            ],
            $svgContent
        );

        // Remove width and height attributes from SVG element only (not from child elements like rect)
        $svgContent = preg_replace('/<svg([^>]*?)\s+(width|height)="[^"]*"/', '<svg$1', $svgContent);
        $svgContent = preg_replace('/<svg([^>]*?)\s+(width|height)="[^"]*"/', '<svg$1', $svgContent); // Run twice in case both attrs present

        // Fix clipPath ID conflicts by making them unique
        if (preg_match_all('/id="(clip[^"]*)"/', $svgContent, $matches)) {
            foreach ($matches[1] as $originalId) {
                $uniqueId = $originalId . '_' . uniqid();
                $svgContent = str_replace(
                    [
                        'id="' . $originalId . '"',
                        'clip-path="url(#' . $originalId . ')"'
                    ],
                    [
                        'id="' . $uniqueId . '"',
                        'clip-path="url(#' . $uniqueId . ')"'
                    ],
                    $svgContent
                );
            }
        }
    } else {
        if (Configure::read('debug')) {
            $svgContent = '<span class="text-red-500">[Icon not found: ' . h($name) . ']</span>';
        }
    }
}

// Render the icon wrapper with inline SVG
?>
<span<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= $svgContent ?>
</span>
