<?php
/**
 * Timestamp Atom Component
 *
 * Displays formatted date/time using semantic HTML time element.
 *
 * @var \App\View\AppView $this
 * @var \Cake\I18n\DateTime|string $datetime DateTime object or string
 * @var string $format PHP date format (default: 'd.m.y - H:i \U\h\r')
 * @var array $options Additional HTML attributes
 */

use Cake\I18n\DateTime;

$datetime = $datetime ?? null;
$format = $format ?? 'd.m.y - H:i \U\h\r';
$options = $options ?? [];

if (!$datetime) {
    return;
}

if (is_string($datetime)) {
    $datetime = new DateTime($datetime);
}

$displayTime = $datetime->format($format);

$isoTime = $datetime->toIso8601String();

$baseClasses = 'text-sm text-gray-500';
$classes = [$baseClasses];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);
$options['datetime'] = $isoTime;
?>

<time<?= $this->Html->templater()->formatAttributes($options) ?>><?= h($displayTime) ?></time>