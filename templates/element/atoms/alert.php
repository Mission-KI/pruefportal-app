<?php
/**
 * Alert Atom Component
 *
 * Bootstrap alert component for flash messages, notifications, and feedback.
 * Supports different alert types with optional icons and dismiss functionality.
 *
 * @var \App\View\AppView $this
 * @var string $message Alert message content (required)
 * @var string $title Alert title (optional)
 * @var string $type Alert type (primary|secondary|success|danger|warning|info|light|dark)
 * @var string $icon Bootstrap icon class (optional, e.g., 'bi bi-check-circle')
 * @var string $size Text size class (e.g., 'text-sm', 'text-md', 'text-lg') - default: ''
 * @var bool $dismissible Whether alert can be dismissed (default: false)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

// Set defaults
$message = $message ?? '';
$title = $title ?? '';
$type = $type ?? 'info';
$icon = $icon ?? '';
$size = $size ?? '';
$dismissible = $dismissible ?? false;
$options = $options ?? [];
$escape = $escape ?? true;

// Validate required fields
if (empty($message)) {
    return; // Don't render alert without message
}

// Build CSS classes
$alertClasses = [
    'alert',
    'alert-' . $type
];

if ($size) {
    $alertClasses[] = $size;
}

if ($dismissible) {
    $alertClasses[] = 'alert-dismissible';
    $alertClasses[] = 'fade';
    $alertClasses[] = 'show';
}

// Add user-provided classes
if (isset($options['class'])) {
    $alertClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $alertClasses);

// Add role for accessibility
if (!isset($options['role'])) {
    $options['role'] = 'alert';
}

// Prepare content with optional icon and title
$content = '';
if ($title) {
    $titleContent = '';
    if ($icon) {
        $titleContent .= $this->element('atoms/icon', [
            'name' => $icon,
            'size' => 'md',
            'options' => ['class' => 'inline-block mr-2']
        ]);
    }
    $titleContent .= h($title);
    $content .= '<h4 class="alert-heading flex items-center gap-2 mb-2 text-md">' . $titleContent . '</h4>';
}
$content .= $escape ? h($message) : $message;

// Add dismiss button if dismissible
if ($dismissible) {
    $content .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
}
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= $content ?>
</div>