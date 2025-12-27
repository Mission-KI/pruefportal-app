<?php
/**
 * Quality Dimension Card Molecule Component
 *
 * Displays a card for a quality dimension in the protection needs analysis.
 * Shows the dimension title, icon, status indicator, and action button.
 *
 * @var \App\View\AppView $this
 * @var string $title Quality dimension title (required)
 * @var string $qd_id Quality dimension ID (required)
 * @var string $icon Icon name for the quality dimension (required)
 * @var string $state Card state: 'incomplete'|'current'|'complete' (required)
 * @var string|array|false $url URL for the action button, or false to hide button (required)
 * @var string $buttonText Text for the action button (default: 'Rate')
 */

$title = $title ?? '';
$qd_id = $qd_id ?? '';
$icon = $icon ?? 'placeholder';
$state = $state ?? 'incomplete';
$url = $url ?? false;
$buttonText = $buttonText ?? __('Rate');

// Map state to card styling
$stateConfig = [
    'incomplete' => [
        'cardClasses' => 'bg-white border-gray-300',
        'textClasses' => 'text-gray-800',
        'iconCircleClasses' => 'bg-blue-50'
    ],
    'current' => [
        'cardClasses' => 'bg-white border-primary',
        'textClasses' => 'text-gray-800',
        'iconCircleClasses' => 'bg-blue-50'
    ],
    'complete' => [
        'cardClasses' => 'bg-brand border-brand',
        'textClasses' => 'text-brand-400',
        'iconCircleClasses' => 'bg-blue-50'
    ]
];

$config = $stateConfig[$state] ?? $stateConfig['incomplete'];
?>

<div class="w-full">
    <div class="h-full flex flex-col items-center justify-between p-6 rounded-lg shadow-md transition-all duration-200 hover:shadow-lg border-2 <?= $config['cardClasses'] ?> relative">
        <?php if ($state === 'incomplete'): ?>
            <div class="absolute top-4 right-4 text-gray-400">
                <?= $this->element('atoms/project_status_icon', [
                    'state' => $state,
                    'options' => ['class' => 'w-6 h-6']
                ]) ?>
            </div>
        <?php elseif ($state === 'complete'): ?>
            <div class="absolute top-4 right-4 text-white-600">
                <?= $this->element('atoms/project_status_icon', [
                    'state' => $state,
                    'options' => ['class' => 'w-6 h-6']
                ]) ?>
            </div>
        <?php else: ?>
            <div class="absolute top-4 right-4">
                <?= $this->element('atoms/project_status_icon', [
                    'state' => $state,
                    'options' => ['class' => 'w-6 h-6']
                ]) ?>
            </div>
        <?php endif; ?>

        <div class="w-20 h-20 rounded-full <?= $config['iconCircleClasses'] ?> flex items-center justify-center mt-2">
            <?= $this->element('atoms/icon', [
                'name' => $icon,
                'size' => 'xl',
                'options' => ['class' => 'text-primary']
            ]) ?>
        </div>

        <h5 class="my-4 text-lg font-medium text-center <?= $config['textClasses'] ?>">
            <?= h($title) ?> (<?= h($qd_id) ?>)
        </h5>

        <?php if ($url !== false): ?>
            <?= $this->element('atoms/button', [
                'label' => $buttonText,
                'url' => $url,
                'variant' => 'primary',
                'size' => 'md',
                'options' => ['class' => 'mt-4']
            ]) ?>
        <?php endif; ?>
    </div>
</div>
