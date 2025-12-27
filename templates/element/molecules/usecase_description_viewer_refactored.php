<?php
/**
 * UseCase Description Viewer Molecule (Refactored)
 *
 * Server-side renderer for displaying saved UseCase Description data.
 * Refactored to match form.php structure with card-based layout.
 *
 * @var \App\View\AppView $this
 * @var string $currentLanguage Current language code (e.g., 'de' or 'en')
 * @var array $flatData Parsed data from UsecaseDescription.getParsedDescription()
 * @var string $mode Display mode: 'view' (detailed) or 'review' (compact)
 * @var \App\Model\Entity\Process $process
 * @var \App\Model\Entity\UsecaseDescription $usecaseDescription
 * @var array $commentReferences Array of field names that have comments
 * @var bool $initialOpen Whether accordions should start open (default: true)
 * @var bool $showEditButtons Whether to show edit buttons in accordion headers (default: false)
 */

use App\Utility\FormRenderer;

$mode = $mode ?? 'view';
$flatData = $flatData ?? [];
$initialOpen = $initialOpen ?? true;
$showEditButtons = $showEditButtons ?? false;

if (!is_array($flatData)) {
    echo '<div class="alert alert-danger">' . __('Invalid UseCase Description data format') . '</div>';
    return;
}

// Initialize FormRenderer to get field configuration
$formRenderer = new FormRenderer($this);
$config = $formRenderer->getConfig();
$maxSteps = $formRenderer->getMaxStep();

// Loop through each step and render the saved data
for ($stepNum = 1; $stepNum <= $maxSteps; $stepNum++) {
    $stepKey = (string)$stepNum;

    if (!isset($config[$stepKey])) {
        continue; // Skip if step not found in config
    }

    $stepConfig = $config[$stepKey];

    // Skip empty steps
    if (empty($stepConfig['fields'])) {
        continue;
    }

    // Check if this step has any data
    $hasStepData = false;
    foreach ($stepConfig['fields'] as $field) {
        $fieldName = $field['name'];
        if (isset($flatData[$fieldName]) && $flatData[$fieldName] !== '') {
            $hasStepData = true;
            break;
        }
    }

    // Skip steps with no data (unless in view mode, show all steps)
    if (!$hasStepData && $mode === 'review') {
        continue;
    }
    ?>

    <!-- Step Section (Collapsible) -->
    <div class="step-section mb-8" x-data="{open: <?= $initialOpen ? 'true' : 'false' ?>}">
        <!-- Step Header (collapsible trigger) -->
        <div
            class="w-full bg-gray-100 px-6 py-4 mb-4 flex items-center justify-between hover:bg-gray-200 transition-colors cursor-pointer"
            @click="open = !open"
            :aria-expanded="open"
            role="button"
            tabindex="0"
            @keydown.enter="open = !open"
            @keydown.space.prevent="open = !open"
        >
            <h3 class="text-lg font-semibold text-brand">
                <?= $stepNum ?>. <?= h($stepConfig['title'][$currentLanguage] ?? '') ?>
            </h3>

            <div class="flex items-center gap-3">
                <?php if ($showEditButtons): ?>
                    <!-- Edit Button -->
                    <button
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand"
                        @click.stop="$store.formState.currentStep = <?= $stepNum ?>"
                    >
                        <?= __('Edit') ?>
                    </button>
                <?php endif; ?>

                <!-- Chevron Icon -->
                <div class="flex-shrink-0">
                    <?= $this->element('atoms/icon', [
                        'name' => 'chevron-down',
                        'size' => 'sm',
                        'options' => [
                            'class' => 'transform transition-transform',
                            ':class' => "{'rotate-180': open}"
                        ]
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Fields Display (collapsible content) -->
        <div class="space-y-4 px-6 py-4" x-show="open" x-collapse>
            <?php foreach ($stepConfig['fields'] as $field): ?>
                <?php
                $fieldName = $field['name'];
                $fieldValue = $flatData[$fieldName] ?? null;

                // Skip if no value and in review mode
                if (($fieldValue === null || $fieldValue === '') && $mode === 'review') {
                    continue;
                }

                // Process value based on field type
                $displayValue = $fieldValue;

                // For radio/select, get the label of the selected option
                if (in_array($field['type'], ['radio', 'select']) && isset($field['options'])) {
                    $selectedLabel = null;
                    foreach ($field['options'] as $option) {
                        if ((string)$option['value'] === (string)$fieldValue) {
                            $selectedLabel = $option['label'][$currentLanguage] ?? $option['label'];
                            break;
                        }
                    }
                    $displayValue = $selectedLabel ?? $fieldValue;
                }

                // For file type, create a special display
                $isFileField = false;
                if ($field['type'] === 'file' && $fieldValue) {
                    $displayValue = $this->Html->link(
                        $this->element('atoms/icon', [
                            'name' => 'file-save',
                            'size' => 'md'
                        ]) . ' ' . __('View file'),
                        ['controller' => 'Uploads', 'action' => 'ajaxView', urlencode($fieldValue)],
                        [
                            'class' => 'js-load-upload inline-flex items-center gap-2 text-brand-light-web hover:text-brand-deep transition-colors',
                            'escape' => false
                        ]
                    );
                    $isFileField = true;
                }

                // Render the read-only field (mimics form_field structure)
                echo $this->element('molecules/form_field_readonly', [
                    'index' => $field['index'] ?? null,
                    'label' => $field['label'][$currentLanguage] ?? '',
                    'value' => $displayValue,
                    'tooltip' => $field['tooltip'][$currentLanguage] ?? null,
                    'help' => $field['help'][$currentLanguage] ?? null,
                    'fieldName' => $fieldName,
                    'processId' => $process->id,
                    'commentReferences' => $commentReferences,
                    'escape' => !$isFileField
                ]);

                // Render modal for comments (one per field)
                echo $this->element('molecules/modal', [
                    'id' => 'modal' . strtolower($fieldName),
                    'title' => __('Comment for {0}', $fieldName),
                    'content' => 'Loading',
                    'size' => 'md'
                ]);
                ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php
}
?>
