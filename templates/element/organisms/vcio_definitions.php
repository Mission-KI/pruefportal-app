<?php
/**
 * VCIO Definitions Organism
 *
 * Displays VCIO level definitions (A, B, C, D) in tabs within an accordion.
 *
 * @var \App\View\AppView $this
 * @var array $indicatorContent Indicator content containing A, B, C, D definitions
 */

$definitions = array_intersect_key($indicatorContent, array_flip(['A', 'B', 'C', 'D']));

if (empty($definitions)) {
    return;
}

$tabs = [];
foreach ($definitions as $key => $definition) {
    $tabs[] = [
        'id' => 'def-' . $key,
        'label' => __('Definition für {0}', $key),
        'content' => '<div class="text-sm">' . $definition . '</div>'
    ];
}

$tabsHtml = $this->element('molecules/tabs', [
    'tabs' => $tabs,
    'escape' => false
]);

$accordionItems = [
    [
        'title' => __('Definitionen und Kriterien'),
        'content' => $tabsHtml,
        'escape' => false
    ]
];
?>
<div class="mb-4 p-0">
    <?= $this->element('molecules/accordion', ['items' => $accordionItems]) ?>
</div>
