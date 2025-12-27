<?php
/**
 * Navigation Link Atom Component
 *
 * Standardized navigation link with optional icon and consistent styling.
 * Used for sidebar navigation, breadcrumbs, and menu items.
 *
 * @var \App\View\AppView $this
 * @var string $text Link text content (required)
 * @var array $url CakePHP URL array for the link destination (required)
 * @var string $icon Bootstrap icon class (optional, e.g., 'bi bi-home')
 * @var string $variant Link style variant (nav-link|side-nav-item|breadcrumb|custom)
 * @var bool $active Whether this link is currently active
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

// Set defaults
$text = $text ?? '';
$url = $url ?? [];
$icon = $icon ?? '';
$variant = $variant ?? 'nav-link';
$active = $active ?? false;
$options = $options ?? [];
$escape = $escape ?? true;

// Validate required fields
if (empty($text) || empty($url)) {
    return; // Don't render link without text and URL
}

// Build CSS classes based on variant
$linkClasses = [];

switch ($variant) {
    case 'nav-link':
        $linkClasses[] = 'nav-link';
        if ($active) {
            $linkClasses[] = 'active';
        }
        break;
    case 'side-nav-item':
        $linkClasses[] = 'side-nav-item';
        if ($active) {
            $linkClasses[] = 'active';
        }
        break;
    case 'breadcrumb':
        $linkClasses[] = 'breadcrumb-item';
        if ($active) {
            $linkClasses[] = 'active';
        }
        break;
    case 'custom':
        // No default classes for custom variant
        break;
    default:
        $linkClasses[] = $variant;
        break;
}

// Add user-provided classes
if (isset($options['class'])) {
    $linkClasses[] = $options['class'];
    unset($options['class']);
}

if (!empty($linkClasses)) {
    $options['class'] = implode(' ', $linkClasses);
}

// Prepare content with optional icon
$content = '';
if ($icon) {
    $iconHtml = '<i class="' . h($icon) . ($text ? ' me-2' : '') . '"></i>';
    $content .= $iconHtml;
}
$content .= $escape ? h($text) : $text;

// Set escape to false since we're handling it manually
$options['escape'] = false;
?>

<?= $this->Html->link($content, $url, $options) ?>