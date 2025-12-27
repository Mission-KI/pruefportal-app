<?php
/**
 * @var \App\View\AppView $this
 * @var string $initials 2-letter initials (required)
 * @var string $full_name Full name for alt text (optional)
 * @var string $image_url Avatar image URL (optional)
 * @var string $size Size variant: xs|sm|md|lg|xl (default: md)
 * @var string $color Background color class (optional)
 * @var bool|null $online_status Show online status indicator (true = online, false/null = no indicator)
 * @var array $options Additional HTML attributes
 */

$initials = $initials ?? '';
$full_name = $full_name ?? '';
$image_url = $image_url ?? '';
$size = $size ?? 'md';
$bgColor = $color ?? 'var(--color-brand-lightest)';
$online_status = $online_status ?? null;
$options = $options ?? [];

if (empty($initials) && empty($image_url)) {
    return;
}

$classes = ['mki-avatar', "mki-avatar--{$size}", 'relative'];

if (!$image_url) {
    $classes[] = 'mki-avatar--initials';
}

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);

if ($full_name && !isset($options['title'])) {
    $options['title'] = h($full_name);
}
?>

<?php if ($image_url): ?>
    <div<?= $this->Html->templater()->formatAttributes($options) ?>>
        <img src="<?= h($image_url) ?>"
             alt="<?= h($full_name ?: $initials) ?>"
             class="w-full h-full object-cover">
        <?php if ($online_status === true): ?>
            <span class="absolute bottom-0 right-0 block h-2 w-2 rounded-full bg-success-500 ring-2 ring-white"></span>
        <?php endif; ?>
    </div>
<?php else: ?>
    <span<?= $this->Html->templater()->formatAttributes($options) ?>>
        <?php
        $cleanInitials = \App\Utility\StringHelper::stripEmojis($initials);
        echo h(mb_strtoupper(mb_substr($cleanInitials, 0, 2)));
        ?>
        <?php if ($online_status === true): ?>
            <span class="absolute bottom-0 right-0 block h-2 w-2 rounded-full bg-success-500 ring-2 ring-white"></span>
        <?php endif; ?>
    </span>
<?php endif; ?>
