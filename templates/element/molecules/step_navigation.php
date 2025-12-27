<?php
/**
 * Step Navigation Molecule
 *
 * Displays a linear step-by-step navigation for process workflows.
 * Supports status-based styling and icons for upcoming, current, and completed steps.
 *
 * @var \App\View\AppView $this
 * @var string $title Navigation title
 * @var array $steps Array of step objects with structure:
 *   [
 *     'title' => string,           // Step title
 *     'status' => string,          // 'upcoming', 'current', 'completed'
 *     'url' => array|string|null,  // Link URL (null if completed and not clickable)
 *     'key' => string|null,        // Optional identifier to display (e.g., "TR", "1.1")
 *     'current_label' => string    // Optional label for current step (default: "Currently editing")
 *   ]
 * @var string $completed_icon Icon name for completed steps (default: 'check-circle')
 * @var string $completed_icon_class CSS classes for completed icon (default: 'text-green-500 ml-2')
 * @var array $options Additional CSS classes for the nav container
 */

$title = $title ?? '';
$steps = $steps ?? [];
$completed_icon = $completed_icon ?? 'check-circle';
$completed_icon_class = $completed_icon_class ?? 'text-green-500 ml-2';
$current_label_default = $current_label_default ?? __('Currently editing');
$options = $options ?? [];

$containerClass = 'bg-white rounded-lg shadow-sm border border-gray-200 p-4';
if (!empty($options['class'])) {
    $containerClass .= ' ' . $options['class'];
}
?>

<nav class="<?= h($containerClass) ?>">
    <?php if (!empty($title)): ?>
        <h3 class="text-md font-semibold text-brand mb-4"><?= h($title) ?></h3>
    <?php endif; ?>

    <ol class="space-y-3">
        <?php foreach ($steps as $step): ?>
            <?php
            $status = $step['status'] ?? 'upcoming';
            $stepTitle = $step['title'] ?? '';
            $url = $step['url'] ?? null;
            $key = $step['key'] ?? null;
            $currentLabel = $step['current_label'] ?? $current_label_default;
            ?>

            <li class="relative flex items-start">
                <?php if ($status === 'completed'): ?>
                    <!-- Completed step: show with check icon (clickable if URL provided) -->
                    <div class="flex items-center justify-between w-full">
                        <?php if ($url): ?>
                            <?= $this->Html->link(
                                h($stepTitle) . ($key ? ' (' . h($key) . ')' : ''),
                                $url,
                                ['class' => 'text-brand text-md underline hover:bg-gray-50 hyphens-auto break-words']
                            ) ?>
                        <?php else: ?>
                            <span class="text-brand text-md hyphens-auto break-words">
                                <?= h($stepTitle) ?>
                                <?php if ($key): ?>
                                    <span class="ml-1">(<?= h($key) ?>)</span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <?= $this->element('atoms/icon', [
                            'name' => $completed_icon,
                            'size' => 'sm',
                            'options' => ['class' => $completed_icon_class]
                        ]) ?>
                    </div>

                <?php elseif ($status === 'current'): ?>
                    <!-- Current step: show with current label -->
                    <div class="flex flex-col">
                        <?php if ($url): ?>
                            <?= $this->Html->link(
                                h($stepTitle) . ($key ? ' (' . h($key) . ')' : ''),
                                $url,
                                ['class' => 'text-brand text-md underline hover:bg-gray-50 hyphens-auto break-words']
                            ) ?>
                        <?php else: ?>
                            <span class="text-brand text-md font-medium hyphens-auto break-words">
                                <?= h($stepTitle) ?>
                                <?php if ($key): ?>
                                    <span class="ml-1">(<?= h($key) ?>)</span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($currentLabel): ?>
                            <span class="mt-1 text-sm text-gray-500"><?= h($currentLabel) ?></span>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Upcoming step: show as link or muted text -->
                    <?php if ($url): ?>
                        <?= $this->Html->link(
                            h($stepTitle) . ($key ? ' (' . h($key) . ')' : ''),
                            $url,
                            ['class' => 'text-brand text-md underline hover:bg-gray-50 hyphens-auto break-words']
                        ) ?>
                    <?php else: ?>
                        <span class="text-gray-500 text-md hyphens-auto break-words">
                            <?= h($stepTitle) ?>
                            <?php if ($key): ?>
                                <span class="ml-1">(<?= h($key) ?>)</span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
