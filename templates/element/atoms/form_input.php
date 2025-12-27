<?php
/**
 * Form Input Atom
 *
 * Generates the same HTML output as CakePHP's Form->control() for input fields.
 * This ensures perfect consistency between FormRenderer (atomic) and FormFieldHelper (CakePHP) approaches.
 *
 * The minimal structure matches CakePHP's input template:
 * '<input type="{{type}}" name="{{name}}" {{attrs}}>'
 *
 * @var string $name Input name attribute
 * @var string $id Input id attribute
 * @var string $type Input type (defaults to 'text')
 * @var string|null $value Input value
 * @var string|null $placeholder Placeholder text
 * @var bool $required Whether field is required
 * @var bool $disabled Whether field is disabled
 * @var array $attributes Additional HTML attributes (including 'class')
 */

// Set defaults
$type = $type ?? 'text';
$id = $id ?? $name; // Default ID to name if not provided
$value = $value ?? null;
$placeholder = $placeholder ?? null;
$required = $required ?? false;
$disabled = $disabled ?? false;
$attributes = $attributes ?? [];

// Build attributes array (no default CSS classes - let caller specify)
$inputAttributes = [
    'type' => $type,
    'name' => $name,
    'id' => $id,
];

if ($value !== null) {
    $inputAttributes['value'] = $value;
}

if ($placeholder !== null) {
    $inputAttributes['placeholder'] = $placeholder;
}

if ($required) {
    $inputAttributes['required'] = true;
}

if ($disabled) {
    $inputAttributes['disabled'] = true;
}

// Merge additional attributes (including class from caller)
$inputAttributes = array_merge($inputAttributes, $attributes);

// Convert attributes to HTML string
$attributeString = '';
foreach ($inputAttributes as $attr => $val) {
    if ($val === true) {
        $attributeString .= ' ' . h($attr);
    } elseif ($val !== false && $val !== null) {
        $attributeString .= ' ' . h($attr) . '="' . h($val) . '"';
    }
}
?>
<input<?= $attributeString ?> />
