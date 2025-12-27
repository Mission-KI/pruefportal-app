<?php
/**
 * @var \App\View\AppView $this
 * @var string $text Navigation text (required)
 * @var array|string $url Link URL (required)
 * @var string|\App\Utility\Icon $icon Icon name (optional)
 * @var bool $active Active state (default: false)
 * @var bool $external External link indicator (default: false)
 * @var array $subitems Array of subitems for expandable navigation (optional)
 * @var bool $expanded Initial expanded state (default: false)
 * @var string|null $modal_trigger Modal ID to trigger on click (optional)
 * @var string|null $modal_url URL to load content from when opening modal (optional)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$text = $text ?? '';
$url = $url ?? '';
$icon = $icon ?? null;
$active = $active ?? false;
$external = $external ?? false;
$subitems = $subitems ?? [];
$expanded = $expanded ?? false;
$modal_trigger = $modal_trigger ?? null;
$modal_url = $modal_url ?? null;
$options = $options ?? [];
$data = $data ?? [];

$hasSubitems = !empty($subitems);

if (empty($text)) {
    return;
}

// Allow items without URL if they have a modal trigger
if (!$hasSubitems && empty($url) && empty($modal_trigger)) {
    return;
}

$classes = ['flex', 'items-center', 'rounded', 'transition-colors', 'no-underline'];
$classes[] = 'p-[var(--nav-item-padding-y)_var(--nav-item-padding-x)]';
$classes[] = 'rounded-[var(--nav-item-border-radius)]';
$classes[] = 'gap-[var(--nav-item-gap)]';
$classes[] = $active ? 'bg-blue-50' : 'bg-transparent';
$classes[] = 'hover:bg-gray-50';

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$iconClass = 'w-[var(--nav-item-icon-size)] h-[var(--nav-item-icon-size)] ' . ($active ? 'text-brand-deep' : 'text-gray-600');
$textClass = 'text-md flex-1 ' . ($active ? 'text-brand-deep font-semibold' : 'text-gray-600' . ($external ? '' : ' font-semibold'));
$chevronClass = 'w-[var(--nav-item-icon-size)] h-[var(--nav-item-icon-size)] text-gray-600 transition-transform duration-200';

$linkOptions = array_merge($options, [
    'class' => implode(' ', $classes),
    'escape' => false
]);

if ($external) {
    $linkOptions['target'] = '_blank';
    $linkOptions['rel'] = 'noopener noreferrer';
}

if ($active) {
    $linkOptions['aria-current'] = 'page';
}

// Handle modal trigger
if ($modal_trigger) {
    $linkOptions['data-modal-trigger'] = $modal_trigger;
    if ($modal_url) {
        $linkOptions['data-modal-url'] = $modal_url;
    }
    $linkOptions['data-no-loading'] = true;
}

// Handle data attributes
foreach ($data as $key => $value) {
    $linkOptions['data-' . $key] = $value;
}

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

<?php if ($hasSubitems): ?>
    <?= $this->Html->link($content, $url, $linkOptions) ?>
    <?php if ($expanded): ?>
        <div class="mt-1 space-y-1">
            <?php foreach ($subitems as $subitem): ?>
                <?= $this->element('atoms/sidebar_nav_subitem', $subitem) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <?= $this->Html->link($content, $url, $linkOptions) ?>
<?php endif; ?>
