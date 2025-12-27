<?php
/**
 * @var \App\View\AppView $this
 * @var int $formIndex
 * @var string $indicatorKey
 * @var string $levelName
 * @var array $levelLabels
 * @var array $observables
 * @var \App\Model\Entity\Indicator|null $existingIndicator
 */

    // Convert answers object to options array for radio group
    $transformedOptions = array_map(
        fn($value, $key) => ['value' => (int) $key, 'label' => $value . $levelLabels[$key]],
        $observables,
        array_keys($observables)
    );
    // Reset array keys if you need sequential numeric keys
    $transformedOptions = array_values($transformedOptions);

    // Get existing value if available
    $existingValue = null;
    if (isset($existingIndicator) && $existingIndicator) {
        $existingValue = $existingIndicator->{$levelName};
    }

    echo $this->element('molecules/form_field', [
        'name' => 'indicators[' . $formIndex . '][' . $levelName . ']',
        'label' => false,
        'type' => 'radio',
        'required' => true,
        'client_error_messages' => [__('Please select a level.')],
        'atom_element' => 'molecules/form_radio_group',
        'atom_data' => [
            'required' => true,
            'name' => 'indicators[' . $formIndex . '][' . $levelName . ']',
            'baseId' => 'vcio_' . $indicatorKey,
            'options' => $transformedOptions,
            'layout' => 'vertical',
            'selectedValue' => $existingValue
        ]
    ]);
?>
