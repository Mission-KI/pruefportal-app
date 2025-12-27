<?php
/**
 * VCIO Level Display Molecule
 *
 * Displays a read-only view of a VCIO level selection (e.g., candidate's self-assessment).
 *
 * @var \App\View\AppView $this
 * @var string $title The section title
 * @var int|null $selectedLevel The selected level (0-3)
 * @var array $levelLabels Labels for each level (keyed by level number)
 * @var array $observables Observable descriptions for each level
 */

$title = $title ?? __('Selbsteinschätzung');
$selectedLevel = $selectedLevel ?? null;
$levelLabels = $levelLabels ?? [];
$observables = $observables ?? [];
?>
<div class="mki-form-field-wrapper mb-4">
    <h3 class="text-lg font-semibold text-brand"><?= h($title) ?></h3>
    <div class="flex gap-2 flex-wrap">
    <?php foreach ($levelLabels as $level_key => $level_name): ?>
        <div class="p-2 border border-gray-200 rounded-lg mb-2<?= ($selectedLevel === $level_key) ? ' bg-brand text-white' : '' ?>">
            <?= h($observables[$level_key] ?? '') . h($level_name) ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
