<?php
/**
 * Form Select Atom
 *
 * Generates the same HTML output as CakePHP's Form->control() for select fields.
 * This ensures perfect consistency between FormRenderer (atomic) and FormFieldHelper (CakePHP) approaches.
 *
 * The minimal structure matches CakePHP's select template:
 * '<select name="{{name}}" class="form-control" {{attrs}}>{{content}}</select>'
 *
 * @var string $name Select name attribute
 * @var string $id Select id attribute
 * @var string|null $value Selected value
 * @var array $options Options array - can be flat array or key-value pairs
 * @var string|null $placeholder Placeholder option text (disabled)
 * @var string|null $empty Empty option text (selectable, like CakePHP's empty parameter)
 * @var bool $required Whether field is required
 * @var bool $disabled Whether field is disabled
 * @var array $attributes Additional HTML attributes (including 'class')
 */

// Set defaults
$value = $value ?? null;
$options = $options ?? [];
$placeholder = $placeholder ?? null;
$empty = $empty ?? null;
$required = $required ?? false;
$disabled = $disabled ?? false;
$attributes = $attributes ?? [];

// Build attributes array (no default CSS classes - let caller specify)
$selectAttributes = [
    'name' => $name,
    'id' => $id,
];

if ($required) {
    $selectAttributes['required'] = true;
}

if ($disabled) {
    $selectAttributes['disabled'] = true;
}

// Merge additional attributes (including class from caller)
$selectAttributes = array_merge($selectAttributes, $attributes);

// Convert attributes to HTML string
$attributeString = '';
foreach ($selectAttributes as $attr => $val) {
    if ($val === true) {
        $attributeString .= ' ' . h($attr);
    } elseif ($val !== false && $val !== null) {
        $attributeString .= ' ' . h($attr) . '="' . h($val) . '"';
    }
}
?>
<select<?= $attributeString ?>>
    <?php if ($placeholder): ?>
        <option value="" <?= $value === null || $value === '' ? 'selected' : '' ?> disabled><?= h($placeholder) ?></option>
    <?php endif; ?>

    <?php if ($empty): ?>
        <option value="" <?= $value === null || $value === '' ? 'selected' : '' ?>><?= h($empty) ?></option>
    <?php endif; ?>

    <?php
    // TODO: Backend team - review optgroup implementation for grouped selects
    // https://github.com/Mission-KI/pruefportal/issues/139

    // Check if options are grouped (nested arrays for optgroups)
    $isGrouped = !empty($options) && is_array(reset($options)) && !isset(reset($options)['label']);
    ?>

    <?php if ($isGrouped): ?>
        <?php foreach ($options as $groupLabel => $groupOptions): ?>
            <optgroup label="<?= h($groupLabel) ?>">
                <?php foreach ($groupOptions as $optionValue => $optionLabel): ?>
                    <?php $selected = ($value !== null && (string)$value === (string)$optionValue) ? 'selected' : ''; ?>
                    <option value="<?= h($optionValue) ?>" <?= $selected ?>><?= h($optionLabel) ?></option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach ($options as $optionKey => $optionData): ?>
            <?php
            // Handle three formats:
            // 1. Flat array: ['Option A', 'Option B']
            // 2. Key-value pairs: ['key1' => 'Label 1', 'key2' => 'Label 2']
            // 3. Label-value objects: [{'label': 'Label 1', 'value': 'key1'}]

            if (is_array($optionData) && isset($optionData['label']) && isset($optionData['value'])) {
                // Format 3: Label-value objects from JSON
                $optionValue = $optionData['value'];
                $optionLabel = $optionData['label'];
            } elseif (is_int($optionKey) && is_string($optionData) && array_keys($options) === range(0, count($options) - 1)) {
                // Format 1: Flat sequential array (0-indexed without gaps)
                $optionValue = $optionData;
                $optionLabel = $optionData;
            } else {
                // Format 2: Key-value pairs (including integer keys that aren't sequential)
                $optionValue = $optionKey;
                $optionLabel = $optionData;
            }

            $selected = ($value !== null && (string)$value === (string)$optionValue) ? 'selected' : '';
            ?>
            <option value="<?= h($optionValue) ?>" <?= $selected ?>><?= h($optionLabel) ?></option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>