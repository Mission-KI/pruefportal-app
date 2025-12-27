<?php
/**
 * Form Textarea Atom
 *
 * Generates the same HTML output as CakePHP's Form->control() for textarea fields.
 * This ensures perfect consistency between FormRenderer (atomic) and FormFieldHelper (CakePHP) approaches.
 *
 * The minimal structure matches CakePHP's textarea template:
 * '<textarea name="{{name}}" class="form-control" {{attrs}}>{{value}}</textarea>'
 *
 * @var string $name Textarea name attribute
 * @var string $id Textarea id attribute
 * @var string|null $value Textarea content
 * @var string|null $placeholder Placeholder text
 * @var bool $required Whether field is required
 * @var bool $disabled Whether field is disabled
 * @var int $rows Number of rows (defaults to 3)
 * @var int $cols Number of columns (optional)
 * @var array $attributes Additional HTML attributes (including 'class')
 */

// Set defaults
$id = $id ?? $name; // Default ID to name if not provided
$value = $value ?? null;
$placeholder = $placeholder ?? null;
$required = $required ?? false;
$disabled = $disabled ?? false;
$rows = $rows ?? 3;
$cols = $cols ?? null;
$attributes = $attributes ?? [];

// Build attributes array (no default CSS classes - let caller specify)
$textareaAttributes = [
    'name' => $name,
    'id' => $id,
    'rows' => $rows,
];

if ($cols !== null) {
    $textareaAttributes['cols'] = $cols;
}

if ($placeholder !== null) {
    $textareaAttributes['placeholder'] = $placeholder;
}

if ($required) {
    $textareaAttributes['required'] = true;
}

if ($disabled) {
    $textareaAttributes['disabled'] = true;
}

// Merge additional attributes (including class from caller)
$textareaAttributes = array_merge($textareaAttributes, $attributes);

// Convert attributes to HTML string
$attributeString = '';
foreach ($textareaAttributes as $attr => $val) {
    if ($val === true) {
        $attributeString .= ' ' . h($attr);
    } elseif ($val !== false && $val !== null) {
        $attributeString .= ' ' . h($attr) . '="' . h($val) . '"';
    }
}
?>
<textarea<?= $attributeString ?>><?= h($value ?? '') ?></textarea>