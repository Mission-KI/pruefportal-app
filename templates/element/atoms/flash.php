<?php
/**
 * Flash Message Atom
 *
 * Reusable flash notification component with icon, message, and dismiss button.
 *
 * @var \App\View\AppView $this
 * @var string $message Flash message content (required)
 * @var string $type Notification type: success|error|warning|info (default: info)
 * @var bool $dismissible Whether to show close button (default: true)
 * @var bool $escape Whether to HTML-escape the message (default: true)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$message = $message ?? '';
$type = $type ?? 'info';
$dismissible = $dismissible ?? true;
$escape = $escape ?? true;
$options = $options ?? [];

if (empty($message)) {
    return;
}

// Type-specific configuration
$typeConfig = [
    'success' => [
        'bg' => 'bg-success-50',
        'border' => 'border-success-200',
        'text' => 'text-success-800',
        'icon_color' => 'text-success-600',
        'hover' => 'hover:text-success-800'
    ],
    'error' => [
        'bg' => 'bg-error-50',
        'border' => 'border-error-200',
        'text' => 'text-error-800',
        'icon_color' => 'text-error-600',
        'hover' => 'hover:text-error-800'
    ],
    'warning' => [
        'bg' => 'bg-warning-50',
        'border' => 'border-warning-200',
        'text' => 'text-warning-800',
        'icon_color' => 'text-warning-600',
        'hover' => 'hover:text-warning-800'
    ],
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-200',
        'text' => 'text-blue-800',
        'icon_color' => 'text-blue-600',
        'hover' => 'hover:text-blue-800'
    ]
];

$config = $typeConfig[$type] ?? $typeConfig['info'];

$classes = ['flex', 'items-center', 'gap-3', 'p-4', 'rounded-lg', 'border', 'shadow-lg', $config['bg'], $config['border'], $config['text']];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
$options['role'] = $options['role'] ?? 'alert';

// Auto-dismiss for success and info only (not error/warning)
$autoDismiss = in_array($type, ['success', 'info']);
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <div class="flex-1">
        <p class="text-sm font-medium"><?= $escape ? h($message) : $message ?></p>
    </div>
    <?php if ($dismissible): ?>
        <button type="button" class="flex-shrink-0 <?= $config['icon_color'] ?> <?= $config['hover'] ?>" onclick="this.parentElement.remove();" aria-label="Close">
            <?= $this->element('atoms/icon', ['name' => Icon::X, 'class' => 'w-5 h-5']) ?>
        </button>
    <?php endif; ?>
</div>

<?php if ($autoDismiss): ?>
<script>
(function() {
    const flashElement = document.currentScript.previousElementSibling;
    setTimeout(() => {
        if (flashElement && flashElement.parentElement) {
            flashElement.remove();
        }
    }, 3000);
})();
</script>
<?php endif; ?>
