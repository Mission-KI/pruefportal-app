<?php
/**
 * Original Form Implementation (Handlebars-based)
 *
 * This is the original form.php preserved for side-by-side comparison.
 * Uses client-side Handlebars template rendering.
 *
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Original Form Implementation (Handlebars)'));

// Simulate the original form data structure for demo
$demoProcess = (object) [
    'title' => 'Demo Process - Original Implementation',
    'status_id' => 1
];

$demoUsecaseDescription = (object) [
    'step' => 1,
    'isNew' => true  // Simple boolean instead of function
];

$demoMaxStep = 8;
$step = (int)($this->request->getQuery('step') ?? 1); // Allow step navigation via URL
$step = max(1, min($step, $demoMaxStep)); // Ensure step is within valid range
?>

<div class="container mx-auto px-4 py-8">
    <!-- Demo Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-4">📜 Original Form Implementation</h1>
        <p class="text-lg text-gray-600 mb-4">
            Original Handlebars-based client-side form rendering for comparison.
        </p>
        <?= $this->element('atoms/button', [
            'type' => 'link',
            'variant' => 'secondary',
            'size' => 'small',
            'label' => '← Back to UI Demo',
            'url' => ['action' => 'index']
        ]) ?>
    </div>

    <div class="alert alert-warning">
        <h4><i class="bi bi-gear"></i> Original: Client-side Handlebars + Bootstrap</h4>
        <p class="mb-0">JavaScript compilation, DOM insertion, larger bundle, no SEO</p>
    </div>

    <!-- Original form structure -->
    <h1 class="mb-3"><?= $demoProcess->title ?></h1>
    <div class="row justify-content-center">
        <div class="col-12">
            <?= $this->element('process_status', ['status_id' => $demoProcess->status_id]); ?>

            <?= $this->Form->create(null, [
                'class' => 'needs-validation',
                'novalidate' => true
            ]) ?>

            <input type="hidden" name="step" value="<?= $step ?>">

            <div title="<?= __('Usecase Description progress bar:') . ' ' . $step . '/' . $demoMaxStep  ?>" class="progress my-4" role="progressbar" aria-label="<?= __('Usecase Description progress bar') ?>" aria-valuenow="<?= $step ?>" aria-valuemin="0" aria-valuemax="<?= $demoMaxStep ?>">
                <div class="progress-bar" style="width: <?= $step * 12.5 ?>%"></div>
            </div>

            <!-- This is where Handlebars would inject the form -->
            <div id="process-form-container" data-step="<?= $step ?>" data-template="usecase-description-form-template">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Loading form via JavaScript (Handlebars)...
                </div>
            </div>

        <?php if($step === $demoMaxStep): ?>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="checkFinishedCompletely" required>
                        <label class="form-check-label" for="checkFinishedCompletely">
                            <?= __('The process is finished completely and correctly') ?>
                        </label>
                    <div class="form-text"><?= __('The examiner will contact you after the review is finished.') ?></div>
                    </div>
                </div>
            </div>

            <?= $this->element('atoms/button', [
                'variant' => 'primary',
                'size' => 'md',
                'label' => __('Accept and submit'),
                'options' => [
                    'type' => 'button',
                    'onclick' => "alert('Form submitted! (Demo only)')",
                    'class' => 'float-end'
                ]
            ]) ?>
        <?php else: ?>
            <?= $this->element('atoms/button', [
                'variant' => 'secondary',
                'size' => 'md',
                'label' => 'Reset',
                'options' => [
                    'type' => 'reset',
                    'class' => 'float-start'
                ]
            ]) ?>
            <?= $this->element('atoms/button', [
                'variant' => 'primary',
                'size' => 'md',
                'label' => __('Next step'),
                'url' => '?step=' . min($step + 1, $demoMaxStep),
                'options' => ['class' => 'float-end']
            ]) ?>
        <?php endif; ?>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
