<?php
/**
 * Form Checkbox Atom
 *
 * Renders a styled checkbox input with optional label and description.
 * Can be used standalone in tables or with label in forms.
 * Matches design system checkbox styling from Figma.
 *
 * @var string|null $name Input name attribute (optional for table checkboxes)
 * @var string|null $id Input id attribute (required if using label)
 * @var string $label Label text for the checkbox (optional)
 * @var array $badge Badge to insert right before the label (optional)
 * @var string|null $description Optional description text below the label
 * @var bool $checked Whether checkbox is checked
 * @var bool $required Whether field is required
 * @var bool $disabled Whether field is disabled
 * @var string|null $value Checkbox value (defaults to '1')
 * @var array $attributes Additional HTML attributes for the input
 * @var bool $standalone Render without wrapper div (for table usage)
 * @var string $size Checkbox size: 'sm' (w-4 h-4) or 'md' (w-5 h-5, default)
 * @var bool $escapeDescription Whether to escape the description text (default: true)
 */


$name = $name ?? null;
$id = $id ?? $name ?? uniqid('checkbox_');
$label = $label ?? '';
$badge = $badge ?? null;
$description = $description ?? null;
$checked = $checked ?? false;
$required = $required ?? false;
$disabled = $disabled ?? false;
$value = $value ?? '1';
$attributes = $attributes ?? [];
$standalone = $standalone ?? false;
$size = $size ?? 'md';
$textSize = $textSize ?? 'text-sm';
$escapeDescription = $escapeDescription ?? true;

$inputAttributes = [
    'type' => 'checkbox',
    'id' => $id,
    'value' => $value,
];

$labelClasses = '';

if ($name) {
    $inputAttributes['name'] = $name;
}

if ($checked) {
    $inputAttributes['checked'] = true;
}

if ($required) {
    $inputAttributes['required'] = true;
}

if ($disabled) {
    $inputAttributes['disabled'] = true;
}

if ($badge) {
    $labelClasses = 'flex gap-2 items-baseline flex-wrap';
}



$inputAttributes = array_merge($inputAttributes, $attributes);

$wrapperClasses = 'flex items-start gap-3';

$sizeClass = $size === 'sm' ? 'w-4 h-4' : 'w-5 h-5';
$checkboxClasses = $sizeClass . ' rounded border border-brand-light-web text-brand-light-web focus:ring-2 focus:ring-brand-light-web focus:ring-offset-0 bg-white disabled:bg-gray-100 disabled:cursor-not-allowed';

if ($size === 'md') {
    $checkboxClasses .= ' mt-0.5';
}

if (!isset($inputAttributes['class'])) {
    $inputAttributes['class'] = $checkboxClasses;
}

$attributeString = '';
foreach ($inputAttributes as $attr => $val) {
    if ($val === true) {
        $attributeString .= ' ' . h($attr);
    } elseif ($val !== false && $val !== null) {
        $attributeString .= ' ' . h($attr) . '="' . h($val) . '"';
    }
}
?>
<?php if ($standalone): ?>
    <?php if((int) $value === 1 && $name): ?>
    <input type="hidden" name="<?= $name ?>" value="0">
    <?php endif; ?>
    <input<?= $attributeString ?> />
<?php else: ?>
<div class="<?= h($wrapperClasses) ?>">

    <?php if((int) $value === 1 && $name): // adding the hidden input (= not checked) to make it work with cakephp form handler ?>
    <input type="hidden" name="<?= $name ?>" value="0">
    <?php endif; ?>

    <input<?= $attributeString ?> />
    <?php if ($label): ?>
        <div class="flex-1 <?= $labelClasses ?>">
            <?php if ($badge): ?>
                <?= $this->element('atoms/badge', $badge) ?>
            <?php endif; ?>
            <label for="<?= h($id) ?>" class="font-semibold text-brand-deep <?= h($textSize) ?> cursor-pointer block">
                <?= h($label) ?>
            </label>
            <?php if ($description): ?>
                <p class="text-gray-600 <?= h($textSize) ?> mt-1 w-full">
                    <?= $escapeDescription ? h($description) : $description ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
