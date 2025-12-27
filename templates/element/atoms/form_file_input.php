<?php
/**
 * Form File Input Atom
 *
 * File input with styled button trigger and optional dropzone support.
 * v1: Button-only mode with hidden file input
 * v2: Dropzone mode with drag-and-drop (future enhancement)
 *
 * @var \App\View\AppView $this
 * @var string $name Form field name
 * @var string $id Input ID
 * @var string|null $accept Accepted file types (e.g., ".pdf,.docx")
 * @var bool $multiple Allow multiple file selection (default: true)
 * @var string $button_label Button text (default: "Attach Files")
 * @var string $button_variant Button style variant (default: "secondary")
 * @var string $button_size Button size (default: "SM")
 * @var string|null $button_icon Icon name (e.g., "paperclip")
 * @var bool $enable_dropzone Enable drag-and-drop zone (default: false - v2 feature)
 * @var string|null $on_change Alpine.js @change handler
 */

$name = $name ?? 'files';
$id = $id ?? 'file-input';
$accept = $accept ?? null;
$multiple = $multiple ?? true;
$button_label = $button_label ?? 'Attach Files';
$button_variant = $button_variant ?? 'secondary';
$button_size = $button_size ?? 'SM';
$button_icon = $button_icon ?? null;
$enable_dropzone = $enable_dropzone ?? false;
$on_change = $on_change ?? null;

$inputAttributes = [
    'type' => 'file',
    'name' => $name . ($multiple ? '[]' : ''),
    'id' => $id,
    'class' => 'hidden'
];

if ($accept) {
    $inputAttributes['accept'] = $accept;
}

if ($multiple) {
    $inputAttributes['multiple'] = true;
}

if ($on_change) {
    $inputAttributes['@change'] = $on_change;
}

$attributeString = '';
foreach ($inputAttributes as $attr => $val) {
    if ($val === true) {
        $attributeString .= ' ' . h($attr);
    } elseif ($val !== false && $val !== null) {
        $attributeString .= ' ' . h($attr) . '="' . h($val) . '"';
    }
}

// Convert kebab-case ID to camelCase for x-ref (Alpine.js doesn't handle hyphens in $refs)
$xRef = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $id))));
?>

<div class="file-input-wrapper">
    <input<?= $attributeString ?> x-ref="<?= h($xRef) ?>">

    <?= $this->element('atoms/button', [
        'label' => $button_label,
        'icon' => $button_icon,
        'variant' => $button_variant,
        'size' => $button_size,
        'click' => '$refs.' . $xRef . '.click()',
        'type' => 'button'
    ]) ?>

    <?php if ($enable_dropzone): ?>
        <!-- TODO: v2 - Dropzone implementation, check Figma design -->
    <?php endif; ?>
</div>
