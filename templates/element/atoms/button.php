<?php
/**
 * Button Atom Component
 *
 * Figma Design System Button implementation using TailwindCSS v4 theme system.
 * Supports 8 variants (primary, secondary, tertiary, ghost, success, error, warning, info) × 3 sizes (XS, SM, MD) × 5 states
 *
 * @var \App\View\AppView $this
 * @var string $label Button text content
 * @var string $variant Button variant (primary|secondary|tertiary|ghost|success|error|warning|info)
 * @var string $size Button size (XS|SM|MD)
 * @var string $icon Icon name from webroot/icons (optional, without .svg)
 * @var string $iconPosition Position of icon relative to label ('before'|'after') - default: 'before'
 * @var array $url CakePHP URL array for link, or null for button element
 * @var string $type Defaults to 'button'
 * @var string $onclick JavaScript onclick handler (HTML onclick attribute)
 * @var string $click Alpine.js @click handler (Alpine.js @click directive)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 * @var bool $disabled Whether button is disabled (default: false)
 */

// Set defaults based on Figma design tokens
$label = $label ?? '';
$variant = $variant ?? 'primary';
$size = $size ?? 'MD';
$icon = $icon ?? '';
$iconPosition = $iconPosition ?? 'before';
$url = $url ?? null;
$type = $type ?? 'button';
$onclick = $onclick ?? null;
$click = $click ?? null;
$options = $options ?? [];
$escape = $escape ?? true;
$disabled = $disabled ?? false;

// Build CSS class using TailwindCSS component classes
$baseClass = 'btn btn-' . strtolower($variant) . ' btn-' . strtolower($size);

// Add user-provided classes
$classes = [$baseClass];
if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

// Handle disabled state
if ($disabled || (isset($options['disabled']) && $options['disabled'])) {
    $options['disabled'] = true;
    // Disabled styling is handled by CSS :disabled pseudo-class
}

// Handle onclick and @click attributes
if ($onclick !== null) {
    $options['onclick'] = $onclick;
}
if ($click !== null) {
    $options['@click'] = $click;
}

$options['class'] = implode(' ', $classes);

// Prepare content with optional icon
$content = '';
if ($icon) {
    // Map button sizes to icon sizes
    $iconSizeMap = [
        'XS' => 'xs',
        'SM' => 'sm',
        'MD' => 'md',
    ];
    $iconSize = $iconSizeMap[strtoupper($size)] ?? 'md';

    // Build icon classes
    $iconClasses = ['btn-icon'];
    if ($label) {
        $iconClasses[] = 'btn-icon-' . $iconPosition;
    }

    // Render icon using the icon atom
    $iconHtml = $this->element('atoms/icon', [
        'name' => $icon,
        'size' => $iconSize,
        'options' => ['class' => implode(' ', $iconClasses)]
    ]);

    // Build content based on icon position
    if ($iconPosition === 'after' && $label) {
        $content = ($escape ? h($label) : $label) . $iconHtml;
    } else {
        $content = $iconHtml . ($escape ? h($label) : $label);
    }
} else {
    $content = $escape ? h($label) : $label;
}

// Determine element type and render
if ($url !== null):
    // For links, merge options but don't overwrite the already-built class
    $linkOptions = array_merge($options, ['escape' => false]);
    ?>
    <?= $this->Html->link($content, $url, $linkOptions) ?>
<?php else: ?>
    <button<?= $this->Html->templater()->formatAttributes($options) ?> type="<?= $type ?>"><?= $content ?></button>
<?php endif; ?>
