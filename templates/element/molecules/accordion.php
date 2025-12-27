<?php
/**
 * Accordion Molecule
 *
 * A container that groups multiple accordion items.
 * Supports single or multiple item expansion modes.
 *
 * @param array $items - Array of accordion items with structure:
 *   [
 *     [
 *       'title' => 'Section Title',
 *       'content' => 'Section content',
 *       'open' => false,         // optional
 *       'escape' => true,        // optional
 *       'id' => 'custom-id'      // optional
 *     ],
 *     // ... more items
 *   ]
 * @param bool $allowMultiple - Allow multiple sections to be open simultaneously (default: true)
 * @param string $variant - Style variant: 'default', 'compact', 'bordered' (default: 'default')
 * @param array $options - Additional HTML attributes for the container
 */

$items = $items ?? [];
$allowMultiple = $allowMultiple ?? true;
$variant = $variant ?? 'default';
$options = $options ?? [];

// Variant-specific styles
$variantClasses = [
    'default' => 'mki-accordion',
    'compact' => 'mki-accordion mki-accordion--compact',
    'bordered' => 'mki-accordion mki-accordion--bordered'
];

$containerClass = $variantClasses[$variant] ?? $variantClasses['default'];

// Prepare container attributes
$containerAttributes = [
    'class' => $containerClass . ' ' . ($options['class'] ?? '')
];

// If not allowing multiple open, add Alpine.js data for managing state
if (!$allowMultiple) {
    $containerAttributes['x-data'] = '{ activeItem: null }';
}

// Merge additional options
$containerAttributes = array_merge($containerAttributes, array_diff_key($options, ['class' => '']));

// Generate unique ID for accordion if not provided
$accordionId = $options['id'] ?? uniqid('accordion_');
?>

<div <?= $this->Html->templater()->formatAttributes($containerAttributes) ?>>
    <?php foreach ($items as $index => $item): ?>
        <?php
        // Generate item ID if not provided
        $itemId = $item['id'] ?? $accordionId . '_item_' . $index;

        // For single-expansion mode, manage the open state
        $itemOpen = $item['open'] ?? false;
        $itemOptions = [];

        if (!$allowMultiple) {
            // Override Alpine.js data for single expansion
            $itemOptions['x-data'] = '{
                get open() { return $parent.activeItem === ' . $index . ' },
                toggle() {
                    $parent.activeItem = this.open ? null : ' . $index . '
                }
            }';

            // Replace the click handler
            $itemOptions['@click'] = 'toggle()';
        }

        // Apply variant-specific item styling for bordered variant
        // (handled by CSS classes in mki-accordion--bordered)
        ?>

        <?= $this->element('atoms/accordion_item', [
            'id' => $itemId,
            'title' => $item['title'] ?? '',
            'content' => $item['content'] ?? '',
            'open' => $itemOpen,
            'escape' => $item['escape'] ?? true,
            'options' => $itemOptions
        ]) ?>
    <?php endforeach; ?>
</div>