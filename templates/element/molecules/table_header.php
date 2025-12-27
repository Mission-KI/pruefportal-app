<?php
/**
 * Table Header Molecule
 *
 * Complete header row with cells.
 *
 * @var \App\View\AppView $this
 * @var array $columns Column definitions (required)
 *   Each column: ['label' => string, 'sortable' => bool, 'sortField' => string, 'width' => string, 'align' => string]
 * @var bool $hasCheckbox Show select-all checkbox (default: false)
 * @var bool $hasActions Show actions column header (default: false)
 * @var array $options Additional HTML attributes for <thead>
 */

$columns = $columns ?? [];
$hasCheckbox = $hasCheckbox ?? false;
$hasActions = $hasActions ?? false;
$options = $options ?? [];

if (empty($columns)) {
    return;
}

$defaultClasses = 'bg-brand-lightest';

$classes = $defaultClasses;
if (isset($options['class'])) {
    $classes .= ' ' . $options['class'];
    unset($options['class']);
}

$attributes = array_merge([
    'class' => $classes,
], $options);
?>

<thead<?= $this->Html->templater()->formatAttributes($attributes) ?>>
    <tr class="border-b border-gray-200">
        <?php if ($hasCheckbox): ?>
            <th class="px-4 py-3 w-10 text-center">
                <?= $this->element('atoms/form_checkbox', [
                    'id' => 'select-all',
                    'standalone' => true,
                    'size' => 'sm',
                    'attributes' => [
                        '@click' => 'toggleAll()',
                        'x-bind:checked' => 'allSelected'
                    ]
                ]) ?>
            </th>
        <?php endif; ?>

        <?php foreach ($columns as $column): ?>
            <?= $this->element('atoms/table_header_cell', [
                'label' => $column['label'] ?? '',
                'sortable' => $column['sortable'] ?? false,
                'sortField' => $column['sortField'] ?? $column['field'] ?? null,
                'width' => $column['width'] ?? null,
                'align' => $column['align'] ?? 'left',
                'options' => $column['headerOptions'] ?? []
            ]) ?>
        <?php endforeach; ?>

        <?php if ($hasActions): ?>
            <?= $this->element('atoms/table_header_cell', [
                'label' => __('Aktion'),
                'align' => 'right',
                'width' => '10%'
            ]) ?>
        <?php endif; ?>
    </tr>
</thead>
