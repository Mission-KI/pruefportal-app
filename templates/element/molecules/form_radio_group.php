<?php
/**
 * Form Radio Group Molecule
 *
 * Groups multiple radio buttons together for a single field.
 * Renders all options for a radio field using the form_radio atom.
 *
 * @var string $name Radio group name (same for all options)
 * @var array $options Array of options - can be flat array or key-value pairs
 * @var string|null $selectedValue Currently selected value
 * @var bool $required Whether field is required
 * @var bool $disabled Whether field is disabled
 * @var string $baseId Base ID for generating unique IDs for each radio
 */

// Set defaults
$options = $options ?? [];
$selectedValue = $selectedValue ?? null;
$required = $required ?? false;
$disabled = $disabled ?? false;
$baseId = $baseId ?? $name;
$relatedClass = $relatedClass ?? null;

?>
<div class="mki-form-radio-group">
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
        } elseif (is_numeric($optionKey)) {
            // Format 1: Flat array
            $optionValue = $optionData;
            $optionLabel = $optionData;
        } else {
            // Format 2: Key-value pairs
            $optionValue = $optionKey;
            $optionLabel = $optionData;
        }

        // Generate unique ID for each radio button
        $radioId = $baseId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $optionValue);
        ?>

        <?= $this->element('atoms/form_radio', [
            'name' => $name,
            'id' => $radioId,
            'value' => $optionValue,
            'label' => $optionLabel,
            'selectedValue' => $selectedValue,
            'required' => $required,
            'disabled' => $disabled,
            'relatedClass' => $relatedClass
        ]) ?>

    <?php endforeach; ?>
</div>
