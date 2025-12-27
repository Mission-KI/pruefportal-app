<?php
/**
 * Loading Spinner Atom
 *
 * Animated spinner using the refresh icon
 *
 * @var string|null $size Size class (default: w-8 h-8)
 * @var string|null $color Color class (default: text-brand)
 */

$size = $size ?? 'w-8 h-8';
$color = $color ?? 'text-brand';
?>
<svg class="<?= h($size) ?> <?= h($color) ?> animate-spin" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M20.453 13.2264C20.1752 15.8363 18.6964 18.282 16.2494 19.6948C12.1839 22.042 6.98539 20.6491 4.63818 16.5836L4.38818 16.1506M3.54613 11.4404C3.82393 8.83048 5.30272 6.38476 7.74971 4.97199C11.8152 2.62478 17.0137 4.01772 19.3609 8.08321L19.6109 8.51622M3.49316 18.3994L4.22521 15.6674L6.95727 16.3994M17.0424 8.26738L19.7744 8.99943L20.5065 6.26738" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
