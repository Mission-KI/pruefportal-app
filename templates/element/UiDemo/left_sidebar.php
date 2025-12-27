<?php
/**
 * UI Demo Left Sidebar
 *
 * Navigation for UI component categories using app_sidebar organism.
 *
 * @var \App\View\AppView $this
 */

use App\Utility\ComponentRegistry;

// Initialize component registry
$registry = new ComponentRegistry();
$showcase = $registry->getShowcaseSections();

// Build sections for app_sidebar
$sidebarSections = [];

foreach (['atoms', 'molecules', 'organisms'] as $category) {
    $categoryShowcase = array_filter($showcase, fn($s) => $s['category'] === $category);
    usort($categoryShowcase, fn($a, $b) => strcmp($a['title'], $b['title']));

    if (!empty($categoryShowcase)) {
        $items = [];
        foreach ($categoryShowcase as $section) {
            $items[] = [
                'text' => $section['title'],
                'url' => '#' . $section['id'],
                'active' => false
            ];
        }

        $sidebarSections[] = [
            'heading' => ucfirst($category) . ' (' . count($categoryShowcase) . ')',
            'items' => $items
        ];
    }
}
?>

<?= $this->element('organisms/app_sidebar', [
    'sections' => $sidebarSections,
    'options' => [
        'style' => 'position: sticky; top: 0; height: 100vh; overflow-y: auto; background-color: var(--color-gray-50); border-right: 1px solid var(--color-gray-200); border-left: none; flex-shrink: 0;'
    ]
]) ?>
