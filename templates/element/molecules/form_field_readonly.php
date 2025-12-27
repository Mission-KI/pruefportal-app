<?php
/**
 * Form Field Read-Only Molecule
 *
 * Mimics form_field.php structure but displays values in a read-only format.
 * Used for view/review templates to maintain visual consistency with the form.
 *
 * @var mixed $index Field index (e.g., UC 1.1) - optional
 * @var string $label Field label
 * @var string|array $value Field value to display
 * @var string|null $tooltip Optional tooltip text
 * @var string|null $help Optional help text
 * @var string $fieldName Field name/ID for comments
 * @var int $processId Process ID for comments
 * @var array $commentReferences Array of field names that have comments
 * @var string|null $containerClass Optional additional CSS classes
 * @var bool $escape Whether to escape HTML content (default: true)
 */

$index = $index ?? null;
$label = $label ?? '';
$value = $value ?? '';
$tooltip = $tooltip ?? null;
$help = $help ?? null;
$fieldName = $fieldName ?? '';
$processId = $processId ?? null;
$commentReferences = $commentReferences ?? [];
$containerClass = $containerClass ?? '';
$escape = $escape ?? true;

$hasComments = in_array($fieldName, $commentReferences);
$commentIcon = $hasComments ? 'annotation' : 'message-plus-square';
$commentUrl = $hasComments
    ? ['controller' => 'Comments', 'action' => 'ajax_view', $processId, $fieldName]
    : ['controller' => 'Comments', 'action' => 'ajax_add', $processId];
$modalId = 'modal' . strtolower($fieldName);

// Build container classes
$containerClasses = ['mki-form-field-container', 'mb-5'];
if (!empty($containerClass)) {
    $containerClasses[] = $containerClass;
}
?>
<div class="<?= implode(' ', $containerClasses) ?>" id="<?= h($fieldName) ?>">
    <div class="mki-form-field-wrapper">
        <?php if ($index !== null): ?>
            <span class="mki-form-field-index-badge" data-reference="<?= h($fieldName) ?>"><?= h($index) ?></span>
        <?php endif; ?>

        <?php if (!empty($label)): ?>
        <div class="mki-form-field-label-wrapper flex items-center justify-between mb-2">
            <div class="text-brand-deep font-normal text-md">
                <?= h($label) ?>
            </div>
            <div class="flex items-center gap-2">
                <?php if (!empty($tooltip)): ?>
                    <div x-data="{ open: false }" class="relative inline-block">
                        <button type="button"
                                @click="open = !open"
                                @click.away="open = false"
                                class="bg-transparent border-none p-0 cursor-pointer flex items-center"
                                :class="{ 'text-brand-light-web': open, 'text-gray-500': !open }">
                            <?= $this->element('atoms/icon', [
                                'name' => 'help-circle',
                                'size' => 'sm',
                                'options' => ['class' => 'w-5 h-5']
                            ]) ?>
                        </button>
                        <div x-show="open"
                             x-transition
                             class="fixed left-[var(--tooltip-left)] -translate-x-1/2 bottom-[var(--tooltip-bottom)] z-[9999] bg-brand-deep text-white p-4 rounded-[var(--radius-md)] shadow-[var(--shadow-lg)] min-w-64 max-w-80"
                             x-init="$watch('open', value => {
                                 if (value) {
                                     const rect = $el.previousElementSibling.getBoundingClientRect();
                                     $el.style.setProperty('--tooltip-left', rect.left + rect.width / 2 + 'px');
                                     $el.style.setProperty('--tooltip-bottom', window.innerHeight - rect.top + 8 + 'px');
                                 }
                             })">
                            <div class="absolute left-1/2 -translate-x-1/2 bottom-[-0.5rem] w-0 h-0 border-l-[0.5rem] border-l-transparent border-r-[0.5rem] border-r-transparent border-t-[0.5rem] border-t-brand-deep"></div>
                            <div class="text-sm leading-normal">
                                <?= h($tooltip) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


            </div>
        </div>
        <?php endif; ?>

        <!-- Read-only Value Display (mimics input styling) -->
        <div class="mki-form-field-readonly-value bg-gray-50 rounded-[var(--radius-md)] p-2 text-gray-900">
            <?php if (is_array($value)): ?>
                <!-- Multi-value display -->
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($value as $item): ?>
                        <li><?= h($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif (empty($value)): ?>
                <span class="text-gray-400 italic"><?= __('No value provided') ?></span>
            <?php else: ?>
                <?= $escape ? nl2br(h($value)) : $value ?>
            <?php endif; ?>
        </div>
    </div>

   <?php if ($fieldName && $processId): ?>
                       <!-- Commentary Button -->
                       <button
                           type="button"
                           class="self-start bg-transparent border-none p-1 cursor-pointer text-brand-light-web hover:text-brand-deep transition-colors"
                           data-modal-trigger="<?= h($modalId) ?>"
                           data-modal-url="<?= $this->Url->build($commentUrl) ?>"
                           data-field-index="<?= h($index) ?>"
                           data-reference-id="<?= h($fieldName) ?>"
                           title="<?= $hasComments ? __('View comments') : __('Add comment') ?>">
                           <?= $this->element('atoms/icon', [
                               'name' => $commentIcon,
                               'size' => 'sm',
                               'options' => ['class' => 'w-5 h-5']
                           ]) ?>
                       </button>
                   <?php endif; ?>
</div>
