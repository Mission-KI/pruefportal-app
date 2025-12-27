<?php
/**
 * Info Popover Molecule
 *
 * Information button with popover tooltip using Bootstrap's popover functionality
 *
 * @var \App\View\AppView $this
 * @var string $title - Popover title
 * @var string $content - Popover content text
 * @var string $icon - Icon name (default: 'info-circle')
 * @var string $size - Icon size: 'xs', 'sm' (default), 'md', 'lg'
 * @var string $placement - Popover placement: 'top', 'bottom', 'left', 'right' (default: 'top')
 * @var string|null $class - Additional CSS classes
 * @var bool $trigger - Trigger type: true for click (default), false for hover
 */

// Set default values
$title = $title ?? __('Information');
$content = $content ?? '';
$icon = $icon ?? 'info-circle';
$size = $size ?? 'sm';
$placement = $placement ?? 'top';
$class = $class ?? '';
$trigger = $trigger ?? true;

// Return empty if no content provided
if (empty($content)) {
    return;
}

// Escape content for safe HTML attributes
$escapedTitle = h($title);
$escapedContent = h($content);
$triggerType = $trigger ? 'click' : 'hover';

// Combine classes
$buttonClasses = "inline-flex items-center justify-center w-6 h-6 ml-1 text-gray-600 hover:text-gray-800 focus:outline-none {$class}";
?>
<button
    type="button"
    class="<?= $buttonClasses ?>"
    data-bs-toggle="popover"
    data-bs-title="<?= $escapedTitle ?>"
    data-bs-content="<?= $escapedContent ?>"
    data-bs-placement="<?= $placement ?>"
    data-bs-trigger="<?= $triggerType ?>"
    aria-label="<?= $escapedTitle ?>"
>
    <?= $this->element('atoms/icon', [
        'name' => $icon,
        'size' => $size,
        'options' => ['class' => 'text-current']
    ]) ?>
</button>