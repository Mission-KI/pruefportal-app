<?php
/**
 * Project Link Atom Component
 *
 * Simple link with arrow prefix for project listings.
 * Styled with purple color and underline.
 *
 * @var \App\View\AppView $this
 * @var string $text Link text content (required)
 * @var array $url CakePHP URL array for the link destination (required)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

// Set defaults
$text = $text ?? '';
$url = $url ?? [];
$options = $options ?? [];
$escape = $escape ?? true;

// Validate required fields
if (empty($text) || empty($url)) {
    return;
}

// Build CSS classes
$linkClasses = [
    'text-primary',
    'border-b-2',
    'border-primary',
    'hover:text-primary-700',
    'inline-flex',
    'items-center',
    'gap-2'
];

// Add user-provided classes
if (isset($options['class'])) {
    $linkClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $linkClasses);

// Prepare content with arrow icon - icon should be part of the link for underline
$content = $this->element('atoms/icon', [
    'name' => 'arrow-right',
    'size' => 'sm'
]) . ' ' . ($escape ? h($text) : $text);

// Set escape to false since we're handling it manually
$options['escape'] = false;
?>

<?= $this->Html->link($content, $url, $options) ?>
