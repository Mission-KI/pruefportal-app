<?php
/**
 * Action Group Molecule
 *
 * Groups related buttons and links with consistent spacing and layout
 *
 * @var \App\View\AppView $this
 * @var array $actions - Array of action items
 * @var string $layout - Layout: 'horizontal' (default), 'vertical', 'grid'
 * @var string $align - Alignment: 'left' (default), 'center', 'right'
 * @var string $gap - Gap size: 'xs', 'sm', 'md' (default), 'lg'
 * @var string|null $class - Additional CSS classes
 *
 * Action item structure:
 * [
 *   'type' => 'button|link',
 *   'label' => 'Button Text',
 *   'url' => '/path/to/action', // for links
 *   'icon' => 'icon-name', // optional
 *   'variant' => 'primary|secondary|danger', // for buttons
 *   'size' => 'xs|sm|md|lg',
 *   'attributes' => ['key' => 'value'] // additional HTML attributes
 * ]
 */

// Set default values
$actions = $actions ?? [];
$layout = $layout ?? 'horizontal';
$align = $align ?? 'left';
$gap = $gap ?? 'md';
$class = $class ?? '';

// Return empty if no actions
if (empty($actions)) {
    return;
}

// Define layout classes
$layoutClasses = [
    'horizontal' => 'flex flex-wrap items-center',
    'vertical' => 'flex flex-col',
    'grid' => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4'
];

// Define alignment classes
$alignClasses = [
    'left' => 'justify-start',
    'center' => 'justify-center',
    'right' => 'justify-end'
];

// Define gap classes
$gapClasses = [
    'xs' => 'gap-1',
    'sm' => 'gap-2',
    'md' => 'gap-3',
    'lg' => 'gap-4'
];

// Combine classes
$containerClasses = implode(' ', [
    $layoutClasses[$layout] ?? $layoutClasses['horizontal'],
    $alignClasses[$align] ?? $alignClasses['left'],
    $gapClasses[$gap] ?? $gapClasses['md'],
    $class
]);
?>
<div class="<?= trim($containerClasses) ?>">
    <?php foreach ($actions as $action): ?>
        <?php
        $actionType = $action['type'] ?? 'button';
        $label = $action['label'] ?? 'Action';
        $url = $action['url'] ?? '#';
        $icon = $action['icon'] ?? null;
        $variant = $action['variant'] ?? 'secondary';
        $size = $action['size'] ?? 'sm';
        $attributes = $action['attributes'] ?? [];
        ?>

        <?php if ($actionType === 'button'): ?>
            <?= $this->element('atoms/button', [
                'label' => $label,
                'url' => $url,
                'icon' => $icon,
                'variant' => $variant,
                'size' => $size,
                'attributes' => $attributes
            ]) ?>
        <?php elseif ($actionType === 'link'): ?>
            <?php
            // Build link attributes
            $linkClass = 'inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 no-underline text-sm';
            $allAttributes = array_merge(['class' => $linkClass], $attributes);
            ?>
            <?= $this->Html->link(
                ($icon ? $this->element('atoms/icon', ['name' => $icon, 'size' => 'sm']) . ' ' : '') . h($label),
                $url,
                array_merge($allAttributes, ['escape' => false])
            ) ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>