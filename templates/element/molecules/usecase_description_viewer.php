<?php
/**
 * UseCase Description Viewer Molecule
 *
 * Server-side renderer for displaying saved UseCase Description data.
 * Replaces Handlebars client-side rendering for view.php and review.php.
 *
 * @var \App\View\AppView $this
 * @var \App\Controller\AppController $currentLanguage Current language code (e.g., 'de' or 'en')
 * @var string $description JSON string from UsecaseDescription.description field
 * @var string $mode Display mode: 'view' (detailed) or 'review' (compact)
 * @var \App\Model\Entity\Process $process
 * @var array $commentReferences
 */

use App\Utility\FormRenderer;

$mode = $mode ?? 'view';
$description = $description ?? '[]';

// The description field is a comma-separated list of JSON objects, not a proper array
// Format: {"step1":"data"},{"step2":"data"},{"step3":"data"}
// We need to wrap it in brackets to make it valid JSON array
$description = '[' . $description . ']';

// Parse the JSON description
$formData = json_decode($description, true);

if (!is_array($formData)) {
    echo '<div class="alert alert-danger">Invalid UseCase Description data format</div>';
    return;
}

// Initialize FormRenderer to get field configuration
$formRenderer = new FormRenderer($this);
$config = $formRenderer->getConfig();

// Loop through each step and render the saved data
foreach ($formData as $stepIndex => $stepData) {
    $stepNumber = $stepIndex + 1; // Steps are 1-indexed in config

    if (!isset($config[(string)$stepNumber])) {
        continue; // Skip if step not found in config
    }

    $stepConfig = $config[(string)$stepNumber];

    // Skip empty steps (like step 9 which has no fields)
    if (empty($stepConfig['fields'])) {
        continue;
    }
    ?>

    <!-- Step Header Card -->
    <div class="card card-primary rounded-lg p-6 mb-6">
        <div class="space-y-2">
            <?php if ($mode === 'view' && !empty($stepConfig['category'][$currentLanguage])): ?>
                <h5 class="text-xl font-semibold text-white"><?= h($stepConfig['category'][$currentLanguage]) ?></h5>
            <?php endif; ?>

            <h2 class="<?= $mode === 'view' ? 'text-3xl' : 'text-2xl' ?> font-bold text-white">
                <?= $stepNumber . '. ' . h($stepConfig['title'][$currentLanguage]) ?>
            </h2>

            <?php if ($mode === 'view' && !empty($stepConfig['description'][$currentLanguage])): ?>
                <p class="text-white text-sm mt-2"><?= nl2br(h($stepConfig['description'][$currentLanguage])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fields Display -->
    <div class="space-y-6 mb-8">
        <?php foreach ($stepConfig['fields'] as $field): ?>
            <?php
            $fieldName = $field['name'];
            $fieldValue = $stepData[$fieldName] ?? null;

            // Skip if no value
            if ($fieldValue === null || $fieldValue === '') {
                continue;
            }
            ?>

            <div class="border-b border-gray-200 pb-3">
                <!-- Field Label with Index Badge -->
                <div class="flex items-start gap-3 mb-2" id="<?= $fieldName ?>"><!-- ref_field -->
                    <?php if (isset($field['index'])): ?>
                    <span class="inline-block bg-blue-600 text-white text-sm font-semibold px-3 py-1 rounded" data-reference="<?= $fieldName ?>">
                        <?= h($field['index']) ?>
                    </span>
                    <?php endif; ?>
                    <div class="flex-1">
                        <<?= $mode === 'view' ? 'h4' : 'h5' ?> class="font-semibold">
                            <?= h($field['label'][$currentLanguage]) ?>
                        </<?= $mode === 'view' ? 'h4' : 'h5' ?>>

                    <!-- Field Value -->
                    <?php if (in_array($field['type'], ['radio', 'select'])): ?>
                        <!-- For radio/select, display the label of the selected option -->
                        <?php
                        $selectedLabel = null;
                        if (isset($field['options'])) {
                            foreach ($field['options'] as $option) {
                                if ((string)$option['value'] === (string)$fieldValue) {
                                    $selectedLabel = $option['label'];
                                    break;
                                }
                            }
                        }
                        ?>
                        <p class="text-gray-800 text-base">
                            <?= h($selectedLabel ?? $fieldValue) ?>
                        </p>
                    <?php elseif($field['type'] === 'file'): ?>
                        <!-- For file -->
                        <div class="flex flex-col items-center p-3 rounded-lg border overflow-hidden my-2">
                            <?= $this->element('atoms/icon', ['name' => 'file-save', 'size' => 'lg', 'options' => ['class' => 'mr-2']]) ?>
                            <?= !is_array($fieldValue) ? $this->Html->link(
                                $this->element('atoms/icon', ['name' => 'refresh', 'animation' => 'spin', 'size' => 'sm', 'options' => ['class' => 'icon-spin mr-2']]) . ' Loading File',
                                ['controller' => 'Uploads', 'action' => 'ajaxView', urlencode($fieldValue)],
                                ['class' => 'js-load-upload hover:text-brand', 'escape' => false]) : __('No file uploaded'); ?>
                        </div>
                    <?php else: ?>
                        <!-- For text/textarea, display the value with preserved line breaks -->
                        <p class="text-gray-800 text-base">
                            <?= nl2br(h($fieldValue)) ?>
                        </p>
                    <?php endif; ?>

                    <?php
                        // Add Comment Icon depending on existing comments to this field
                        $icon = 'message-plus-square';
                        $url = ['controller' => 'Comments', 'action' => 'ajax_add', $process->id];
                        $title = __('Add Comment');
                        if(in_array($fieldName, $commentReferences)) {
                            $icon = 'annotation';
                            $url = ['controller' => 'Comments', 'action' => 'ajax_view', $process->id, $fieldName];
                            $title = __('View Comment');
                        }
                        $url = $this->Url->build($url);
                        echo $this->element('molecules/modal', array (
                            'id' => 'modal'.strtolower($fieldName),
                            'title' => __('Comment for {0}', $fieldName),
                            'content' => 'Loading',
                            'size' => 'md',
                        ));
                        echo '<button data-modal-trigger="modal'.strtolower($fieldName).'" data-modal-url="'.$url.'" data-field-index="'.h($field['index']).'" data-reference-id="'.$fieldName.'" class="mt-2 text-brand hover:text-brand-dark text-sm underline">'.
                            $this->element('atoms/icon', [
                            'name' => $icon,
                            'size' => 'sm',
                        ]).'</button>';
                    ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php
}
?>
