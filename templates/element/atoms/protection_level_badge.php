<?php
/**
 * Protection Level Badge Atom
 *
 * @var int|bool|null $level Protection level value
 *   - int 1 = gering (low protection need)
 *   - int 2 = moderat (moderate protection need)
 *   - int 3+ = hoch (high protection need)
 *   - bool|null = N/A (not assessed or not applicable)
 * @var bool $showBackground Whether to show background color (default: true)
 */

$showBackground = $showBackground ?? true;

if ($level === false || $level === true || $level === null) {
    $label = __('N/A');
    $bgClass = 'bg-gray-100';
    $textClass = 'text-gray-600';
} elseif ($level === 1) {
    $label = __('gering');
    $bgClass = 'bg-success-100';
    $textClass = 'text-success-700';
} elseif ($level === 2) {
    $label = __('moderat');
    $bgClass = 'bg-warning-100';
    $textClass = 'text-warning-700';
} elseif ($level >= 3) {
    $label = __('hoch');
    $bgClass = 'bg-error-100';
    $textClass = 'text-error-700';
} else {
    $label = __('N/A');
    $bgClass = 'bg-gray-100';
    $textClass = 'text-gray-600';
}

$classes = "font-semibold {$textClass}";
if ($showBackground) {
    $classes .= " {$bgClass} px-2 py-1 rounded";
}
?>
<span class="<?= $classes ?>"><?= h($label) ?></span>
