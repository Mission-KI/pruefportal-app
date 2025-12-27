<?php
/**
 * @var \App\View\AppView $this
 * @var array $items Breadcrumb items [{text, url?, icon?, active?}] (required)
 * @var array $options Additional HTML attributes
 */

$items = $items ?? [];
$options = $options ?? [];

if (empty($items)) {
    return;
}

$classes = ['flex', 'items-center', 'gap-2', 'bg-gray-100', 'py-[var(--breadcrumb-padding-y)]', 'px-[var(--breadcrumb-padding-x)]', 'mb-6'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<nav <?= $this->Html->templater()->formatAttributes($containerOptions) ?> aria-label="<?= __('Breadcrumb') ?>">
    <ol class="flex flex-wrap items-center gap-2 list-none m-0 p-0">
        <?php foreach ($items as $index => $item): ?>
            <li class="flex items-center gap-2">
                <?= $this->element('atoms/breadcrumb_item', [
                    'text' => $item['text'] ?? '',
                    'url' => $item['url'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'active' => $item['active'] ?? false
                ]) ?>

                <?php if ($index < count($items) - 1): ?>
                    <span class="text-gray-400 text-sm">/</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
