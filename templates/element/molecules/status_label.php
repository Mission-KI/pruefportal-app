<?php
/**
 * Status Label Molecule Component
 *
 * Displays a status indicator dot with text label in a compact badge format.
 * Used in process cards to show current process state.
 *
 * @var \App\View\AppView $this
 * @var int $status_id Process status ID (required)
 * @var string $label Status label text (required)
 * @var array $options Additional HTML attributes
 */

$status_id = $status_id ?? 0;
$label = $label ?? '';
$options = $options ?? [];

if (empty($label)) {
    return;
}

$classes = [
    'inline-flex',
    'items-center',
    'gap-1.5',
    'px-2',
    'py-1',
    'border',
    'border-gray-200',
    'rounded-[6px]',
    'text-xs',
    'text-gray-700',
    'bg-white'
];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
?>

<span<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= $this->element('atoms/status_indicator', [
        'status_id' => $status_id,
        'size' => 'sm'
    ]) ?>
    <span><?= h($label) ?></span>
</span>
