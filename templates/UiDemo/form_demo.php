<?php
/**
 * Form Renderer Demo - Side-by-side comparison
 *
 * Shows the current Handlebars client-side form rendering vs the new server-side
 * FormRenderer implementation. This template is designed to be easily adapted
 * for replacing the actual form.php template.
 *
 * @var \App\View\AppView $this
 */

use App\Utility\FormRenderer;

$this->assign('title', __('Form Renderer Demo - Handlebars vs Server-side'));

// Initialize FormRenderer
$formRenderer = new FormRenderer($this);

// Demo data - always start at step 1 for demo simplicity
$demoStep = 1; // Always start at step 1
$demoMaxStep = $formRenderer->getMaxStep(); // Get actual max from JSON
?>

<div class="container mx-auto px-4 py-8">
    <!-- Demo Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-4">📝 Form Renderer Demo</h1>
        <p class="text-lg text-gray-600 mb-4">
            Server-side FormRenderer with atomic elements vs current Handlebars implementation.
        </p>

        <?= $this->element('atoms/button', [
            'type' => 'link',
            'variant' => 'secondary',
            'size' => 'small',
            'label' => '← Back to UI Demo',
            'url' => ['action' => 'index']
        ]) ?>
    </div>

    <!-- Alpine.js powered form with step navigation -->
        <div x-data="formStepper" x-init="init()">
            <!-- Restart Form Button (Demo Only) -->
            <div class="mb-4">
                <?= $this->element('atoms/button', [
                    'variant' => 'secondary',
                    'size' => 'md',
                    'label' => '🔄 Restart Form',
                    'options' => [
                        'type' => 'button',
                        '@click' => 'restartForm()',
                        'class' => 'mb-4'
                    ]
                ]) ?>
            </div>
            <?= $this->Form->create(null, [
                'class' => 'needs-validation',
                'novalidate' => true,
                'id' => 'demo-form',
                '@submit.prevent' => 'handleSubmit($event)'
            ]) ?>

            <input type="hidden" name="step" :value="currentStep">

            <!-- Progress bar (Alpine.js reactive) -->
            <div class="progress my-4" role="progressbar"
                 :aria-label="'Usecase Description progress bar'"
                 :aria-valuenow="currentStep"
                 aria-valuemin="0"
                 :aria-valuemax="maxSteps"
                 :title="'Usecase Description progress bar: ' + currentStep + '/' + maxSteps">
                <div class="progress-bar" :style="'width: ' + (currentStep / maxSteps * 100) + '%'"></div>
            </div>

            <?= $this->element('molecules/form_stepper', [
                'currentStep' => $demoStep,
                'maxSteps' => $demoMaxStep,
                'config' => [
                    'demoMode' => true,
                    'processId' => null,
                    'usecaseDescriptionId' => null,
                    'autoSaveInterval' => 5000,
                    'serverData' => []
                ]
            ]) ?>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

