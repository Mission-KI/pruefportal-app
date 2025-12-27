<?php
/**
 * Table Row Molecule
 *
 * Complete data row with cells.
 *
 * @var \App\View\AppView $this
 * @var mixed $row Row data object or array (required)
 * @var array $columns Column definitions matching header (required)
 * @var bool $hasCheckbox Show row checkbox (default: false)
 * @var string|null $actions Actions HTML to render (optional)
 * @var string|int $rowId Unique row identifier (required)
 * @var array $options Additional HTML attributes for <tr>
 */

$row = $row ?? null;
$columns = $columns ?? [];
$hasCheckbox = $hasCheckbox ?? false;
$actions = $actions ?? null;
$rowId = $rowId ?? null;
$options = $options ?? [];

if ($row === null || empty($columns) || $rowId === null) {
    return;
}

$defaultClasses = 'hover:bg-gray-50 transition-colors border-b border-gray-200 last:border-0';

$classes = $defaultClasses;
if (isset($options['class'])) {
    $classes .= ' ' . $options['class'];
    unset($options['class']);
}

$attributes = array_merge([
    'class' => $classes,
], $options);

if ($hasCheckbox) {
    $attributes['x-bind:class'] = "selectedRows.has(" . json_encode($rowId) . ") ? 'bg-brand-50' : ''";
}
?>

<tr<?= $this->Html->templater()->formatAttributes($attributes) ?>>
    <?php if ($hasCheckbox): ?>
        <td class="px-4 py-4 w-10 text-center">
            <?= $this->element('atoms/form_checkbox', [
                'id' => 'checkbox-' . $rowId,
                'value' => $rowId,
                'standalone' => true,
                'size' => 'sm',
                'attributes' => [
                    '@click' => 'toggleRow(' . json_encode($rowId) . ')',
                    'x-bind:checked' => 'selectedRows.has(' . json_encode($rowId) . ')'
                ]
            ]) ?>
        </td>
    <?php endif; ?>

    <?php foreach ($columns as $column): ?>
        <?php
        $field = $column['field'] ?? '';
        $cellContent = '';

        if (isset($column['renderer']) && is_callable($column['renderer'])) {
            $cellContent = $column['renderer']($row, $this);
        } elseif ($field) {
            if (is_array($row)) {
                $cellContent = h($row[$field] ?? '');
            } elseif (is_object($row)) {
                $cellContent = h($row->{$field} ?? '');
            }
        }
        ?>
        <?= $this->element('atoms/table_cell', [
            'content' => $cellContent,
            'align' => $column['align'] ?? 'left',
            'truncate' => $column['truncate'] ?? false,
            'nowrap' => $column['nowrap'] ?? false,
            'options' => $column['cellOptions'] ?? []
        ]) ?>
    <?php endforeach; ?>

    <?php if ($actions !== null): ?>
        <td class="px-6 py-4 whitespace-nowrap text-right">
            <?= $actions ?>
        </td>
    <?php endif; ?>
</tr>
