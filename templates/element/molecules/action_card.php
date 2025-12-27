<?php
/**
 * Action Card Molecule
 *
 * A card component for displaying actions in the right sidebar
 *
 * @var \App\View\AppView $this
 * @var string $title Card title (required)
 * @var array $actions Array of action items [{label, url, icon?, type?}]
 * @var array $options Additional HTML attributes
 */

$title = $title ?? __('Actions');
$actions = $actions ?? [];
$options = $options ?? [];

$classes = ['bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$cardOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<div <?= $this->Html->templater()->formatAttributes($cardOptions) ?>>
    <h3 class="text-sm font-semibold text-gray-900 mb-3"><?= h($title) ?></h3>

    <?php if (!empty($actions)): ?>
        <div class="space-y-2">
            <?php foreach ($actions as $action):
                // Map action type to button variant
                $variant = match($action['type'] ?? 'default') {
                    'primary' => 'primary',
                    'secondary' => 'secondary',
                    'danger' => 'error',
                    default => 'tertiary'
                };
            ?>
                <?= $this->element('atoms/button', [
                    'label' => $action['label'],
                    'url' => $action['url'] ?? '#',
                    'variant' => $variant,
                    'size' => 'SM',
                    'icon' => $action['icon'] ?? '',
                    'options' => ['class' => 'w-full']
                ]) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-sm text-gray-500"><?= __('No actions available') ?></p>
    <?php endif; ?>
</div>