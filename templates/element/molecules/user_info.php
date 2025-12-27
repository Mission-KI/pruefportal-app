<?php
/**
 * User Info Molecule Component
 *
 * Displays user name and optional role with flexible orientation.
 *
 * @var \App\View\AppView $this
 * @var string $full_name User full name (required)
 * @var string $role User role/title (optional)
 * @var string $orientation Layout orientation ('horizontal' | 'vertical')
 * @var array $options Additional HTML attributes
 */

$full_name = $full_name ?? '';
$role = $role ?? '';
$orientation = $orientation ?? 'horizontal';
$options = $options ?? [];

if (empty($full_name)) {
    return;
}

if ($orientation === 'vertical') {
    $containerClasses = 'flex flex-col min-w-0';
} else {
    $containerClasses = 'flex items-center gap-2 min-w-0';
}

$classes = [$containerClasses];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <span class="font-semibold text-md text-gray-900 truncate whitespace-normal"><?= h($full_name) ?></span>
    <?php if ($role): ?>
        <span class="text-sm text-gray-600 truncate"><?= h($role) ?></span>
    <?php endif; ?>
</div>
