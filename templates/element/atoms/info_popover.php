<?php
/**
 * Info Popover Atom Component
 *
 * Information button with Bootstrap popover for displaying additional context.
 * Provides consistent styling and behavior for help/info tooltips.
 *
 * @var \App\View\AppView $this
 * @var string $title Popover title (required)
 * @var string $content Popover content text (required)
 * @var string $icon Bootstrap icon class (default: 'bi bi-info-circle')
 * @var string $placement Popover placement (top|bottom|left|right, default: 'top')
 * @var string $trigger Popover trigger (click|hover|focus, default: 'click')
 * @var string $size Button size (xs|sm|md|lg, default: 'xs')
 * @var array $options Additional HTML attributes for the button
 * @var bool $escape Whether to escape content (default: true)
 */

// Set defaults
$title = $title ?? '';
$content = $content ?? '';
$icon = $icon ?? 'bi bi-info-circle';
$placement = $placement ?? 'top';
$trigger = $trigger ?? 'click';
$size = $size ?? 'xs';
$options = $options ?? [];
$escape = $escape ?? true;

// Validate required fields
if (empty($title) || empty($content)) {
    return; // Don't render popover without title and content
}

// Build button classes
$buttonClasses = [
    'btn',
    'btn-link',
    'btn-' . $size,
    'p-0',
    'border-0',
    'text-primary'
];

// Add user-provided classes
if (isset($options['class'])) {
    $buttonClasses[] = $options['class'];
    unset($options['class']);
}

// Set button attributes
$buttonOptions = array_merge([
    'type' => 'button',
    'class' => implode(' ', $buttonClasses),
    'data-bs-toggle' => 'popover',
    'data-bs-placement' => $placement,
    'data-bs-trigger' => $trigger,
    'data-bs-title' => $escape ? h($title) : $title,
    'data-bs-content' => $escape ? h($content) : $content,
    'tabindex' => '0'
], $options);

// Add accessibility attributes
if (!isset($buttonOptions['aria-label'])) {
    $buttonOptions['aria-label'] = 'More information about: ' . ($escape ? h($title) : strip_tags($title));
}
?>

<button<?= $this->Html->templater()->formatAttributes($buttonOptions) ?>>
    <i class="<?= h($icon) ?>"></i>
</button>