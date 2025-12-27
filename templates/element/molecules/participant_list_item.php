<?php
/**
 * Participant List Item Molecule Component
 *
 * Displays a participant with avatar, name, and role badge in a clean horizontal layout.
 * Mobile-responsive with proper spacing and alignment.
 *
 * @var \App\View\AppView $this
 * @var string $initials User initials for avatar (required)
 * @var string $full_name User full name (required)
 * @var string $role User role label (e.g., 'Owner', 'Examiner', 'Prüfer/in')
 * @var string $role_variant Badge variant for role (optional, default: 'secondary')
 * @var array $options Additional HTML attributes
 */

$initials = $initials ?? '';
$full_name = $full_name ?? '';
$role = $role ?? '';
$role_variant = $role_variant ?? 'secondary';
$options = $options ?? [];

if (empty($initials) || empty($full_name)) {
    return;
}

$classes = [
    'flex',
    'items-center',
    'gap-3',
    'py-3',
    'px-3',
    'border-b',
    'border-gray-200',
    'hover:bg-gray-50',
    'transition-colors',
    'last:border-b-0'
];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= $this->element('molecules/user_badge', [
        'avatar_initials' => $initials,
        'full_name' => $full_name,
        'role' => $role
    ]) ?>
</div>
