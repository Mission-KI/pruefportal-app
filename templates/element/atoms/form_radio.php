<?php
/**
 * Form Radio Atom
 *
 * Radio button styled according to Figma specifications.
 * Creates a single radio input with associated label.
 *
 * @var string $name Radio name attribute (same for all options in group)
 * @var string $id Radio id attribute (unique for each option)
 * @var string $value Radio value attribute
 * @var string $label Label text for this radio option
 * @var string|null $selectedValue Currently selected value for comparison
 * @var bool $required Whether field is required
 * @var bool $disabled Whether field is disabled
 * @var array $attributes Additional HTML attributes for the input
 */

// Set defaults
$value = $value ?? '';
$label = $label ?? '';
$selectedValue = $selectedValue ?? null;
$required = $required ?? false;
$disabled = $disabled ?? false;
$attributes = $attributes ?? [];
$relatedClass = $relatedClass ?? null;

// Build attributes array
$radioAttributes = [
    'type' => 'radio',
    'name' => $name,
    'id' => $id,
    'value' => $value,
    'class' => 'mki-form-radio' . $relatedClass
];

// Check if this option is selected
if ($selectedValue !== null && (string)$selectedValue === (string)$value) {
    $radioAttributes['checked'] = true;
}

if ($required) {
    $radioAttributes['required'] = true;
}

if ($disabled) {
    $radioAttributes['disabled'] = true;
}

// Merge additional attributes
$radioAttributes = array_merge($radioAttributes, $attributes);

// Convert attributes to HTML string
$attributeString = '';
foreach ($radioAttributes as $attr => $val) {
    if ($val === true) {
        $attributeString .= ' ' . h($attr);
    } elseif ($val !== false && $val !== null) {
        $attributeString .= ' ' . h($attr) . '="' . h($val) . '"';
    }
}
?>
<div class="mki-form-radio-wrapper">
    <input<?= $attributeString ?> />
    <label for="<?= h($id) ?>" class="mki-form-radio-label"><?= \App\Utility\HtmlSanitizer::sanitizeTooltip($label) ?></label>
</div>
