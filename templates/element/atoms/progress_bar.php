<?php
/**
 * Progress Bar Atom Component
 *
 * Bootstrap progress bar component for showing completion status.
 * Supports different styles, colors, and accessibility features.
 * Uses flex layout with external percentage label for better readability.
 *
 * @var \App\View\AppView $this
 * @var int|float $value Current progress value (required)
 * @var int|float $max Maximum progress value (default: 100)
 * @var string $variant Progress bar color (primary|secondary|success|info|warning|danger)
 * @var bool $striped Whether to show striped pattern (default: false)
 * @var bool $animated Whether to animate stripes (default: false, requires striped=true)
 * @var string $label Custom progress label text (optional, overrides percentage display)
 * @var string $aria_label Accessibility label (optional, auto-generated if not provided)
 * @var array $options Additional HTML attributes for the outer container
 * @var bool $escape Whether to escape label content (default: true)
 * @var bool $show_label Whether to show the percentage label (default: true)
 */

// Set defaults
$value = $value ?? 0;
$max = $max ?? 100;
$variant = $variant ?? '';
$striped = $striped ?? false;
$animated = $animated ?? false;
$label = $label ?? '';
$aria_label = $aria_label ?? '';
$options = $options ?? [];
$escape = $escape ?? true;
$show_label = $show_label ?? true;

// Calculate percentage
$percentage = $max > 0 ? ($value / $max) * 100 : 0;
$percentage = max(0, min(100, $percentage)); // Clamp between 0-100

// Build outer container classes (flex layout)
$containerClasses = ['flex', 'items-center', 'gap-2'];

// Add user-provided classes to outer container
if (isset($options['class'])) {
    $containerClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $containerClasses);

// Build progress container classes (with default gray background and min-width)
$progressClasses = ['progress', 'bg-gray-200', 'flex-1', 'rounded-xs', 'min-w-24'];

// Build progress bar classes (with default height)
$barClasses = ['progress-bar', 'h-2', 'rounded-xs'];

if ($variant) {
    $barClasses[] = 'bg-' . $variant;
}

if ($striped) {
    $barClasses[] = 'progress-bar-striped';
}

if ($animated && $striped) {
    $barClasses[] = 'progress-bar-animated';
}

// Prepare accessibility attributes for progress bar
$barAttributes = [
    'class' => implode(' ', $barClasses),
    'role' => 'progressbar',
    'style' => 'width: ' . $percentage . '%',
    'aria-valuenow' => $value,
    'aria-valuemin' => '0',
    'aria-valuemax' => $max
];

// Add aria-label
if ($aria_label) {
    $barAttributes['aria-label'] = $aria_label;
} else {
    $barAttributes['aria-label'] = 'Progress: ' . $percentage . '%';
}

// Prepare label content for external display
$labelContent = '';
if ($show_label) {
    if ($label) {
        $labelContent = $escape ? h($label) : $label;
    } else {
        // Show percentage
        $labelContent = round($percentage) . '%';
    }
}
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <!-- Progress bar container -->
    <div class="<?= implode(' ', $progressClasses) ?>">
        <div<?= $this->Html->templater()->formatAttributes($barAttributes) ?>>
            <!-- Empty - label is now external -->
        </div>
    </div>

    <!-- External label with darker text -->
    <?php if ($show_label && $labelContent): ?>
        <span class="text-gray text-sm font-medium whitespace-nowrap">
            <?= $labelContent ?>
        </span>
    <?php endif; ?>
</div>
