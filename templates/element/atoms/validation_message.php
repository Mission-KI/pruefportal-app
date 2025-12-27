<?php
/**
 * Validation Message Atom Component
 *
 * Form validation feedback component for error and success messages.
 * Provides consistent styling for form field validation states.
 *
 * @var \App\View\AppView $this
 * @var string $message Validation message text (required)
 * @var string $type Message type (invalid|valid|help)
 * @var string $icon Bootstrap icon class (optional)
 * @var array $options Additional HTML attributes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

// Set defaults
$message = $message ?? '';
$type = $type ?? 'invalid';
$icon = $icon ?? '';
$options = $options ?? [];
$escape = $escape ?? true;

// Validate required fields
if (empty($message)) {
    return; // Don't render message without content
}

// Build CSS classes based on type
$messageClasses = [];

switch ($type) {
    case 'invalid':
        $messageClasses[] = 'invalid-feedback';
        break;
    case 'valid':
        $messageClasses[] = 'valid-feedback';
        break;
    case 'help':
        $messageClasses[] = 'form-text';
        break;
    default:
        $messageClasses[] = $type;
        break;
}

// Add user-provided classes
if (isset($options['class'])) {
    $messageClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $messageClasses);

// Prepare content with optional icon
$content = '';
if ($icon) {
    $content .= '<i class="' . h($icon) . ' me-1"></i>';
}
$content .= $escape ? h($message) : $message;
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= $content ?>
</div>