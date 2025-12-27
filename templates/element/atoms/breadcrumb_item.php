<?php
/**
 * @var \App\View\AppView $this
 * @var string $text Item text (required)
 * @var array|string $url Link URL (optional)
 * @var string|\App\Utility\Icon $icon Icon name for first item (optional)
 * @var bool $active Is current page (default: false)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$text = $text ?? '';
$url = $url ?? null;
$icon = $icon ?? null;
$active = $active ?? false;
$options = $options ?? [];

if (empty($text)) {
    return;
}

$classes = ['inline-flex', 'items-center'];
$iconClass = $active ? 'text-brand-deep' : 'text-gray-600';
$textClass = 'text-sm ' . ($active ? 'text-brand-deep font-semibold' : 'text-gray-600');

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$content = '';

if ($icon) {
    $content .= $this->element('atoms/icon', [
        'name' => $icon,
        'size' => 'sm',
        'options' => ['class' => $iconClass]
    ]);
    // If icon is present, hide the text (Home breadcrumb)
} else {
    $content .= '<span class="' . $textClass . '">' . h($text) . '</span>';
}

$options['class'] = implode(' ', $classes);
?>

<?php if ($active): ?>
    <span<?= $this->Html->templater()->formatAttributes($options) ?> aria-current="page">
        <?= $content ?>
    </span>
<?php elseif ($url): ?>
    <?= $this->Html->link($content, $url, array_merge($options, ['escape' => false, 'class' => ($options['class'] ?? '') . ' no-underline'])) ?>
<?php else: ?>
    <span<?= $this->Html->templater()->formatAttributes($options) ?>>
        <?= $content ?>
    </span>
<?php endif; ?>
