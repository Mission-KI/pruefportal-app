<?php
/**
 * Classification Badge Atom
 *
 * @var string $value Classification value (A, B, C, D, or N/A)
 * @var bool $outline Whether to render as outline style (default: false)
 * @var string|null $title Optional title attribute for tooltip
 */

$value = $value ?? 'N/A';
$outline = $outline ?? false;
$title = $title ?? null;

if ($outline) {
    $colorClasses = 'bg-transparent border border-gray-300 text-gray-400';
    $sizeClasses = 'text-xs px-1.5 py-0.5';
} else {
    $sizeClasses = 'text-base px-3 py-1.5';
    switch ($value) {
        case 'A':
            $colorClasses = 'bg-gray-700 text-white';
            break;
        case 'B':
            $colorClasses = 'bg-gray-500 text-white';
            break;
        case 'C':
            $colorClasses = 'bg-gray-300 text-gray-900';
            break;
        case 'D':
            $colorClasses = 'bg-gray-100 text-gray-900';
            break;
        default:
            $colorClasses = 'bg-gray-100 text-gray-600';
    }
}
?>
<span class="font-semibold <?= $colorClasses ?> <?= $sizeClasses ?> rounded"<?php if ($title): ?> title="<?= h($title) ?>"<?php endif; ?>><?= h($value) ?></span>
