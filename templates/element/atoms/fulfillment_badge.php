<?php
/**
 * Fulfillment Badge Atom
 *
 * @var string $value Fulfillment value (ja, nein, or N/A)
 */

$value = $value ?? 'N/A';

if ($value === 'ja') {
    $textClass = 'text-success-700';
} elseif ($value === 'nein') {
    $textClass = 'text-error-700';
} else {
    $textClass = 'text-gray-600';
}
?>
<span class="font-semibold <?= $textClass ?>"><?= h($value) ?></span>
