<?php
/**
 * Form Navigation Molecule
 *
 * Displays navigation for multi-step forms with step indicators
 *
 * @var \App\View\AppView $this
 * @var \App\Controller\AppController $currentLanguage
 * @var array $steps Array of step configurations [{number, title, status: 'completed'|'current'|'upcoming'}]
 * @var int $currentStep Current step number (1-based)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$steps = $steps ?? [];
$currentStep = $currentStep ?? 1;
$dbStep = $dbStep ?? 0;
$options = $options ?? [];
$headline = $headline ?? __('Form Navigation');

$classes = ['bg-white rounded-lg shadow-sm border border-gray-200 p-4'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$navOptions = array_merge($options, [
    'class' => implode(' ', $classes)
]);
?>

<nav <?= $this->Html->templater()->formatAttributes($navOptions) ?>>
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><?= $headline ?></h3>

    <ol class="space-y-3 step-navigation-list">
        <?php foreach ($steps as $step):
            $stepNumber = $step['number'] ?? 0;
            $stepTitle = $step['title'][$currentLanguage] ?? '';
        ?>
            <li class="relative flex items-start step-navigation-item"
                x-data="{
                    get isCompletedInDb() { return <?= $stepNumber ?> <= <?= $dbStep ?>; },
                    get isCurrent() { return <?= $stepNumber ?> === $store.formState.currentStep; },
                    get isEnabled() { return <?= $stepNumber ?> <= <?= $dbStep + 1 ?>; },
                    get showCheckIcon() { return this.isCompletedInDb; },
                    get needsPurpleBackground() { return this.isCompletedInDb || <?= $stepNumber ?> === <?= $dbStep + 1 ?>; }
                }">
                <?php if ($step !== end($steps)): ?>
                    <div class="absolute left-4 top-8 w-0.5 h-full"
                         :class="isCompletedInDb ? 'bg-purple-600' : 'bg-gray-200'"></div>
                <?php endif; ?>

                <div class="relative flex items-center">
                    <span class="step-indicator flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold text-white"
                          :class="{
                              'bg-purple-600': needsPurpleBackground,
                              'bg-gray-200 text-gray-600': !needsPurpleBackground,
                              'ring-4 ring-purple-100': isCurrent
                          }"
                          x-text="showCheckIcon ? '✓' : <?= $stepNumber ?>">
                    </span>
                </div>

                <div class="ml-4 flex-1">
                    <a href="#"
                       data-step="<?= h($stepNumber) ?>"
                       class="text-sm hover:underline step-navigation-link hyphens-auto break-words"
                       :class="{
                           'text-brand-light-web font-semibold': isCurrent,
                           'text-brand-deep': !isCurrent && isEnabled,
                           'text-gray-500': !isEnabled
                       }"
                       :aria-current="isCurrent ? 'step' : null"
                       @click="if (!isEnabled) { $event.preventDefault(); return false; }"
                       :style="!isEnabled ? 'cursor: not-allowed; opacity: 0.6;' : ''">
                        <?= h($stepTitle) ?>
                    </a>
                    <p class="mt-1 text-xs text-gray-500" x-show="isCurrent" x-cloak><?= __('Currently editing') ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
