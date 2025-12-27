<?php
/**
 * Criterion Assessment Card Molecule
 *
 * Displays assessment results for a single quality dimension criterion.
 * Shows icon, title, pass/fail status, rating bar, and protection needs bar.
 *
 * @var \App\View\AppView $this
 * @var string $title Criterion title (required)
 * @var string $abbreviation Short code (e.g., 'DA', 'TR') (required)
 * @var string $icon Icon name (required)
 * @var bool $passed Whether criterion passed assessment (required)
 * @var float|int $rating Rating value 0-3 (D=0, C=1, B=2, A=3) (required)
 * @var float|int $protection_needs Protection needs value 0-3 (N/A=0, Niedrig=1, Moderat=2, Hoch=3)
 * @var bool $not_rated Whether criterion was not rated (default: false)
 * @var array $options Additional HTML attributes
 */

// Set defaults
$title = $title ?? '';
$abbreviation = $abbreviation ?? '';
$icon = $icon ?? 'dots-vertical';
$passed = $passed ?? false;
$rating = $rating ?? 0;
$protection_needs = $protection_needs ?? 0;
$not_rated = $not_rated ?? false;
$options = $options ?? [];

// Validate required fields
if (empty($title) || empty($abbreviation)) {
    if (\Cake\Core\Configure::read('debug')) {
        echo '<span class="text-red-500">[CriterionAssessmentCard: title and abbreviation required]</span>';
    }
    return;
}

// Build card classes
$cardClasses = [
    'criterion-assessment-card',
    'relative',
    'rounded-lg',
    'border-2',
    'border-white/30',
    'p-6',
    'space-y-4'
];

if (isset($options['class'])) {
    $cardClasses[] = $options['class'];
    unset($options['class']);
}
$options['class'] = implode(' ', $cardClasses);

// Rating labels (D to A)
$ratingLabels = ['D', 'C', 'B', 'A'];

// Protection needs labels
$protectionLabels = ['N/A', 'Niedrig', 'Moderat', 'Hoch'];
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <!-- Header: Icon, Title, Status -->
    <div class="flex items-start justify-between gap-4">
        <!-- Icon and Title -->
        <div class="flex items-center gap-4 flex-1">
            <?= $this->element('atoms/icon', [
                'name' => $icon,
                'size' => 'xl',
                'options' => ['class' => 'text-white flex-shrink-0']
            ]) ?>
            <h3 class="text-white font-semibold text-lg leading-tight">
                <?= h($title) ?> (<?= h($abbreviation) ?>)
            </h3>
        </div>

        <!-- Pass/Fail Status Icon -->
        <div class="flex-shrink-0">
            <?php if ($not_rated): ?>
                <?= $this->element('atoms/icon', [
                    'name' => 'x-circle',
                    'size' => 'xl',
                    'options' => ['class' => 'text-white/40']
                ]) ?>
            <?php elseif ($passed): ?>
                <?= $this->element('atoms/icon', [
                    'name' => 'check-circle',
                    'size' => 'xl',
                    'options' => ['class' => 'text-success-400']
                ]) ?>
            <?php else: ?>
                <?= $this->element('atoms/icon', [
                    'name' => 'x-circle',
                    'size' => 'xl',
                    'options' => ['class' => 'text-white/40']
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($not_rated): ?>
        <!-- Not Rated Message -->
        <div class="text-white/80 text-sm italic py-4">
            Nicht bewertet
        </div>
    <?php else: ?>
        <!-- Rating Section -->
        <div>
            <div class="text-white text-center text-base font-normal mb-3">Bewertung</div>
            <?= $this->element('atoms/rating_bar', [
                'value' => $rating,
                'labels' => $ratingLabels,
                'min' => 0,
                'max' => 3,
                'color' => 'white',
                'bg_color' => 'white/20'
            ]) ?>
        </div>

        <!-- Protection Needs Section -->
        <div>
            <div class="text-white text-center text-base font-normal mb-3">Schutzbedarf</div>
            <?= $this->element('atoms/rating_bar', [
                'value' => $protection_needs,
                'labels' => $protectionLabels,
                'min' => 0,
                'max' => 3,
                'color' => 'white',
                'bg_color' => 'white/20'
            ]) ?>
        </div>
    <?php endif; ?>
</div>
