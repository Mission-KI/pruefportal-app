<?php
/**
 * @var \App\View\AppView $this
 * @var string $text Subitem text (required)
 * @var array|string $url Link URL (required)
 * @var string|\App\Utility\Icon|null $icon Icon name (optional)
 * @var bool $active Active state (default: false)
 * @var array $options Additional HTML attributes
 */

$text = $text ?? '';
$url = $url ?? '';
$icon = $icon ?? null;
$active = $active ?? false;
$options = $options ?? [];

if (empty($text) || empty($url)) {
    return;
}

$classes = ['flex', 'items-center', 'transition-colors', 'no-underline', 'text-sm'];
$classes[] = 'py-2';
$classes[] = 'pl-[var(--nav-submenu-indent)]';
$classes[] = 'pr-[var(--nav-item-padding-x)]';
$classes[] = 'rounded-[var(--nav-item-border-radius)]';
$classes[] = 'gap-2';
$classes[] = $active ? 'bg-blue-50' : 'bg-transparent';
$classes[] = 'hover:bg-gray-50';

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$linkOptions = array_merge($options, [
    'class' => implode(' ', $classes),
    'escape' => false
]);

if ($active) {
    $linkOptions['aria-current'] = 'page';
}

$iconClass = 'w-4 h-4 ' . ($active ? 'text-brand-deep' : 'text-gray-600');
$textClass = 'flex-1 ' . ($active ? 'text-brand-deep font-semibold' : 'text-gray-600');

$content = '';

if ($icon) {
    $content .= $this->element('atoms/icon', [
        'name' => $icon,
        'size' => 'sm',
        'options' => ['class' => $iconClass]
    ]);
}

$content .= '<span class="' . $textClass . '">' . h($text) . '</span>';
?>

<?= $this->Html->link($content, $url, $linkOptions) ?>
