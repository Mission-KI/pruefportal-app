<?php
/**
 * @var \App\View\AppView $this
 * @var string $heading Section heading (optional)
 * @var array $items Navigation items [{text, url, icon, active?, external?}] (required)
 * @var bool $show_divider Show top border divider (default: false)
 * @var array $options Additional HTML attributes
 */

$heading = $heading ?? null;
$items = $items ?? [];
$show_divider = $show_divider ?? false;
$options = $options ?? [];

if (empty($items)) {
    return;
}

$classes = [];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$styles = ['padding: var(--nav-section-padding);'];

if ($show_divider) {
    $styles[] = 'border-top: 1px solid var(--color-gray-200);';
    $styles[] = 'margin-top: 1rem;';
    $styles[] = 'padding-top: 1rem;';
}

$containerOptions = array_merge($options, [
    'class' => implode(' ', $classes),
    'style' => implode(' ', $styles)
]);
?>

<div <?= $this->Html->templater()->formatAttributes($containerOptions) ?>>
    <?php if ($heading): ?>
        <?= $this->element('atoms/section_heading', [
            'text' => $heading,
            'variant' => 'sidebar',
            'level' => 'h6'
        ]) ?>
    <?php endif; ?>

    <nav>
        <ul class="list-none m-0 p-0 space-y-1">
            <?php foreach ($items as $item): ?>
                <li>
                    <?= $this->element('atoms/sidebar_nav_item', [
                        'text' => $item['text'] ?? '',
                        'url' => $item['url'] ?? '',
                        'icon' => $item['icon'] ?? '',
                        'active' => $item['active'] ?? false,
                        'external' => $item['external'] ?? false,
                        'subitems' => $item['subitems'] ?? [],
                        'expanded' => $item['expanded'] ?? false,
                        'modal_trigger' => $item['modal_trigger'] ?? null,
                        'modal_url' => $item['modal_url'] ?? null,
                    ]) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>
