<?php
/**
 * Disclaimer Molecule
 *
 * Reusable disclaimer component that can be used as a checkbox (for forms) or as info text (for display)
 *
 * @var \App\View\AppView $this
 * @var string $type Type of disclaimer display: 'checkbox' or 'card' (default: 'checkbox')
 * @var string $title Disclaimer title (default: 'Disclaimer')
 * @var string $text Disclaimer text (default: standard disclaimer text)
 * @var string $name Form field name (for checkbox type, default: 'accept_disclaimer')
 * @var string $id Form field ID (for checkbox type, default: 'accept-disclaimer')
 * @var bool $required Whether checkbox is required (default: true)
 * @var bool $escapeDescription Whether to escape the description text (default: true)
 * @var array $options Additional options
 */

$type = $type ?? 'checkbox';
$title = $title ?? __('Disclaimer');
$name = $name ?? 'accept_disclaimer';
$id = $id ?? 'accept-disclaimer';
$required = $required ?? true;
$textSize = $textSize ?? 'text-sm';
$escapeDescription = $escapeDescription ?? true;
$attributes = $attributes ?? [];
$options = $options ?? [];

// Default disclaimer text
$defaultText = __('Die angebotene Selbstprüfung dient ausschließlich der freiwilligen internen Bewertung durch das teilnehmende Unternehmen. Sie stellt keine behördliche Prüfung, Zertifizierung oder rechtsverbindliche Bewertung dar. acatech übernimmt keine Gewähr für Vollständigkeit, Richtigkeit oder rechtliche Wirkung der Ergebnisse dieser Selbstprüfung. Die Nutzung erfolgt auf eigenes Risiko und in eigener Verantwortung des teilnehmenden Unternehmens. acatech haftet nur bei Vorsatz oder grober Fahrlässigkeit sowie in Fällen gesetzlicher Haftung.');

$text = $text ?? $defaultText;

if ($type === 'checkbox') {
    // Render as checkbox for forms
    $checkboxOptions = [
        'name' => $name,
        'id' => $id,
        'label' => $title,
        'description' => $text,
        'required' => $required,
        'value' => '1',
        'textSize' => $textSize,
        'escapeDescription' => $escapeDescription,
        'attributes' => $attributes
    ];

    // Pass through custom options (like Alpine.js bindings, etc.)
    if (!empty($options)) {
        $checkboxOptions = array_merge($checkboxOptions, $options);
    }

    echo $this->element('atoms/form_checkbox', $checkboxOptions);
} else {
    // Render as card for display/info
    echo $this->element('molecules/card', [
        'title' => $title,
        'body' => $text,
        'variant' => 'secondary',
        'options' => $options
    ]);
}
?>
