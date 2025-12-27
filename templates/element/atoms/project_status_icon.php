<?php
/**
 * Project Status Icon Atom Component
 *
 * Displays a step icon (complete/current/incomplete) for project status.
 * Used in process cards to show overall project progress.
 *
 * @var \App\View\AppView $this
 * @var string $state Project state: 'complete'|'current'|'incomplete' (required)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$state = $state ?? 'incomplete';
$options = $options ?? [];

// Map state to icon and color
$stateConfig = [
    'complete' => [
        'icon' => Icon::STEP_COMPLETE->value,
        'color' => 'text-brand-deep'
    ],
    'current' => [
        'icon' => Icon::STEP_CURRENT->value,
        'color' => 'text-brand-light-web'
    ],
    'incomplete' => [
        'icon' => Icon::STEP_INCOMPLETE->value,
        'color' => 'text-gray-400'
    ]
];

$config = $stateConfig[$state] ?? $stateConfig['incomplete'];

$classes = ['inline-flex', 'items-center', 'flex-shrink-0', $config['color']];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
?>

<?= $this->element('atoms/icon', array_merge([
    'name' => $config['icon']
], $options)) ?>
