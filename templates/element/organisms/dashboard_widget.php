<?php
/**
 * Dashboard Widget Organism
 *
 * Reusable card container for dashboard sections with optional process filter
 *
 * @var \App\View\AppView $this
 * @var string $icon Icon name for header
 * @var string $title Widget title
 * @var array|null $processes Optional processes for filter dropdown
 * @var int|null $process_id Currently selected process ID
 * @var string|null $filter_redirect Redirect parameter for filter (default: current action)
 * @var string $content Main widget content (required)
 * @var string|null $footer Optional footer content (buttons, actions, etc.)
 * @var array $options Additional HTML attributes for container
 */

$icon = $icon ?? 'square';
$title = $title ?? '';
$processes = $processes ?? null;
$process_id = $process_id ?? null;
$filter_redirect = $filter_redirect ?? null;
$content = $content ?? '';
$footer = $footer ?? null;
$options = $options ?? [];

$classes = ['bg-white', 'rounded-lg', 'shadow-sm', 'p-6'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<div<?= $this->Html->templater()->formatAttributes($containerOptions) ?>>
    <!-- Header with icon, title, and optional filter -->
    <div class="sm:flex items-baseline justify-between mb-6">
        <div class="mb-6 sm:mb-0 flex items-center gap-2">
            <?= $this->element('atoms/icon', [
                'name' => $icon,
                'options' => ['class' => 'icon icon-md text-brand-deep']
            ]) ?>
            <h3 class="text-xl font-semibold text-brand-deep"><?= h($title) ?></h3>
        </div>

        <?php if (!empty($processes)): ?>
            <div class="w-80">
                <?= $this->element('molecules/process_filter_bar', [
                    'redirect' => $filter_redirect,
                    'processes' => $processes,
                    'process_id' => $process_id,
                    'onChange' => '$el.submit()'
                ]) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main content -->
    <?= $content ?>

    <!-- Optional footer -->
    <?php if ($footer): ?>
        <div class="mt-6">
            <?= $footer ?>
        </div>
    <?php endif; ?>
</div>
