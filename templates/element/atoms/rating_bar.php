<?php
/**
 * Rating Bar Atom Component
 *
 * A specialized progress bar for displaying assessment ratings with labeled steps.
 * Shows a horizontal bar with value markers and optional current value highlighting.
 *
 * @var \App\View\AppView $this
 * @var float|int $value Current rating value (required)
 * @var array $labels Array of label strings for each step (required)
 * @var float|int $min Minimum value (default: 0)
 * @var float|int $max Maximum value (default: count of labels - 1)
 * @var bool $show_current_value Whether to highlight current value (default: true)
 * @var string $color Bar fill color (default: 'white')
 * @var string $bg_color Background color (default: 'white/20')
 * @var array $options Additional HTML attributes
 */

// Set defaults
$value = $value ?? 0;
$labels = $labels ?? [];
$min = $min ?? 0;
$max = $max ?? (count($labels) > 0 ? count($labels) - 1 : 100);
$show_current_value = $show_current_value ?? true;
$color = $color ?? 'white';
$bg_color = $bg_color ?? 'white/20';
$options = $options ?? [];

// Validate
if (empty($labels)) {
    if (\Cake\Core\Configure::read('debug')) {
        echo '<span class="text-red-500">[RatingBar: labels required]</span>';
    }
    return;
}

// Calculate percentage
$range = $max - $min;
$percentage = $range > 0 ? (($value - $min) / $range) * 100 : 0;
$percentage = max(0, min(100, $percentage));

// Build container classes
$containerClasses = ['rating-bar'];
if (isset($options['class'])) {
    $containerClasses[] = $options['class'];
    unset($options['class']);
}
$options['class'] = implode(' ', $containerClasses);

// Number of steps (spaces between labels)
$stepCount = count($labels) - 1;
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <!-- Progress bar with border -->
    <div class="relative h-8 rounded-lg border-2 border-brand-light bg-transparent mb-2 p-1">
        <div
            class="h-full rounded-md bg-brand-light transition-all duration-300"
            style="width: <?= $percentage ?>%"
            role="progressbar"
            aria-valuenow="<?= $value ?>"
            aria-valuemin="<?= $min ?>"
            aria-valuemax="<?= $max ?>"
        ></div>
    </div>

    <!-- Labels -->
    <div class="flex justify-between text-sm text-white/60 font-normal">
        <?php foreach ($labels as $index => $label): ?>
            <?php
                // Calculate if this label corresponds to current value
                $isActive = $show_current_value && (
                    ($stepCount > 0 && abs(($value - $min) - ($index / $stepCount * $range)) < ($range / $stepCount / 2))
                    || ($index === 0 && $value <= $min)
                    || ($index === count($labels) - 1 && $value >= $max)
                );
                $labelClasses = $isActive ? 'font-bold text-white underline decoration-2 underline-offset-2' : '';
            ?>
            <span class="<?= $labelClasses ?>">
                <?= h($label) ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>
