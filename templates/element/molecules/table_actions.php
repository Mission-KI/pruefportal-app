<?php
/**
 * Table Actions Molecule
 *
 * Action buttons group for table rows.
 *
 * @var \App\View\AppView $this
 * @var array $actions Action definitions (required)
 *   Each action: [
 *     'icon' => string,
 *     'url' => array|string,
 *     'title' => string,
 *     'method' => string (optional, for post links),
 *     'confirm' => string (optional, confirmation message),
 *     'class' => string (optional, additional CSS classes)
 *   ]
 * @var array $options Additional HTML attributes for wrapper
 */

$actions = $actions ?? [];
$options = $options ?? [];

if (empty($actions)) {
    return;
}

$defaultClasses = 'flex items-center gap-2 justify-end';

$classes = $defaultClasses;
if (isset($options['class'])) {
    $classes .= ' ' . $options['class'];
    unset($options['class']);
}
?>

<div class="<?= h($classes) ?>">
    <?php foreach ($actions as $action): ?>
        <?php
        $icon = $action['icon'] ?? null;
        $url = $action['url'] ?? '#';
        $title = $action['title'] ?? '';
        $method = $action['method'] ?? null;
        $confirm = $action['confirm'] ?? null;
        $actionClass = $action['class'] ?? '';

        $linkOptions = [
            'class' => 'text-gray-400 hover:text-brand transition-colors ' . $actionClass,
            'escape' => false,
            'title' => $title,
        ];

        $iconHtml = $icon ? $this->element('atoms/icon', [
            'name' => $icon,
            'size' => 'sm'
        ]) : '';
        ?>

        <?php if ($method === 'post' || $method === 'delete'): ?>
            <?= $this->Form->postLink(
                $iconHtml,
                $url,
                array_merge($linkOptions, [
                    'method' => $method,
                    'confirm' => $confirm
                ])
            ) ?>
        <?php else: ?>
            <?= $this->Html->link(
                $iconHtml,
                $url,
                $linkOptions
            ) ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
