<?php
/**
 * Table Header Cell Atom
 *
 * Individual header cell with optional sort indicator.
 *
 * @var \App\View\AppView $this
 * @var string $label Header text (required)
 * @var bool $sortable Whether column is sortable (default: false)
 * @var string|null $sortField Field name for sorting (required if sortable)
 * @var string|null $width Column width (e.g., "15%", "200px")
 * @var string $align Text alignment (default: "left")
 * @var array $options Additional HTML attributes
 */

$label = $label ?? '';
$sortable = $sortable ?? false;
$sortField = $sortField ?? null;
$width = $width ?? null;
$align = $align ?? 'left';
$options = $options ?? [];

$defaultClasses = 'px-6 py-3 text-xs font-medium text-gray-500 tracking-wider';
$alignClasses = [
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right',
];

$classes = $defaultClasses . ' ' . ($alignClasses[$align] ?? $alignClasses['left']);

if (isset($options['class'])) {
    $classes .= ' ' . $options['class'];
    unset($options['class']);
}

$attributes = array_merge([
    'class' => $classes,
], $options);

if ($width) {
    $attributes['width'] = $width;
}
?>

<th<?= $this->Html->templater()->formatAttributes($attributes) ?>>
    <?php if ($sortable && $sortField): ?>
        <?php
        $buttonAlignClass = match($align) {
            'right' => 'justify-end',
            'center' => 'justify-center',
            default => 'justify-start'
        };
        ?>
        <button @click="sort('<?= h($sortField) ?>')" class="w-full flex items-center gap-1 hover:text-gray-700 transition-colors <?= $buttonAlignClass ?>">
            <?= h($label) ?>
            <?= $this->element('atoms/icon', [
                'name' => 'chevron-selector',
                'size' => 'xs',
                'options' => [
                    'class' => 'transition-opacity',
                    'x-bind:class' => "sortField === '" . h($sortField) . "' ? 'opacity-100' : 'opacity-30'"
                ]
            ]) ?>
        </button>
    <?php else: ?>
        <?= h($label) ?>
    <?php endif; ?>
</th>
