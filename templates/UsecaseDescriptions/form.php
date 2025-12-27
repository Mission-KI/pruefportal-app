<?php
/**
 * Usecase Description Form - Server-side FormRenderer Implementation
 *
 * New implementation using FormRenderer utility and atomic CakePHP elements
 * with Alpine.js progressive enhancement for step navigation and auto-save.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var \App\Model\Entity\UsecaseDescription $usecaseDescription
 * @var int $maxStep
 */

use App\Utility\FormRenderer;

$title_for_layout = __('Process') . ': ' . $process->title;
$this->assign('title', $title_for_layout);

// Initialize FormRenderer
$formRenderer = new FormRenderer($this);

// Get current step: prioritize query param (UI flow), fallback to db.step + 1 (fresh load)
$step = $this->request->getQuery('step');
if (!$step) {
    $step = ($usecaseDescription->isNew()) ? 1 : (int) $usecaseDescription->step + 1;
} else {
    $step = (int) $step;
}

// Set up right sidebar content
$this->start('right_sidebar');

// Actions card
echo $this->element('molecules/action_card', [
    'title' => __('Actions'),
    'actions' => [
        [
            'label' => __('Save Draft'),
            'url' => '#save-draft',
            'type' => 'primary',
            'icon' => 'file-save'
        ],
//        [
//            'label' => __('Add comment'),
//            'url' => ['controller' => 'Projects', 'action' => 'addComment'],
//            'type' => 'primary',
//            'icon' => 'message-plus-square'
//        ]
    ]
]);

// Form navigation - read the same config as FormRenderer
$configPath = WWW_ROOT . 'js' . DS . 'json' . DS . 'UseCaseDescriptionConfig.json';
$config = json_decode(file_get_contents($configPath), true);

$formSteps = [];
foreach ($config as $stepNum => $stepConfig) {
    $formSteps[] = [
        'number' => (int)$stepNum,
        'title' => $stepConfig['title'] ?? '',
        // Don't set status here - will be determined dynamically by Alpine
    ];
}

echo $this->element('molecules/form_navigation', [
    'steps' => $formSteps,
    'currentStep' => $step,
    'dbStep' => ($usecaseDescription->isNew()) ? 0 : (int) $usecaseDescription->step
]);

$this->end();

// Enable right sidebar by setting a non-empty value
$this->assign('show_right_sidebar', 'true');

// Reserve space for the right sidebar to prevent content from spreading underneath it
$this->assign('reserve_sidebar_space', 'true');

// Get max steps from FormRenderer - JSON config is the single source of truth
$maxStep = $formRenderer->getMaxStep();
?>

<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<div class="row justify-content-center">
    <div class="col-12">
        <?= $this->element('process_status', ['process' => $process]); ?>

        <!-- Alpine.js powered form with step navigation -->
        <div x-data="formStepper" x-init="init()">
            <?= $this->element('molecules/form_stepper', [
                'entity' => $usecaseDescription,
                'currentStep' => $step,
                'maxSteps' => $maxStep,
                'config' => [
                    'demoMode' => false,
                    'processId' => $process->id,
                    'usecaseDescriptionId' => $usecaseDescription->id ?? null,
                    'autoSaveInterval' => 15000,
                    'serverData' => $serverData ?? []
                ]
            ]) ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:initialized', function() {
    // Handle step navigation clicks
    document.querySelectorAll('.step-navigation-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const targetStep = parseInt(this.dataset.step);
            const dbStep = <?= ($usecaseDescription->isNew()) ? 0 : (int) $usecaseDescription->step ?>;
            const maxEnabledStep = dbStep + 1;

            // Only allow navigation to enabled steps (based on db.step)
            if (targetStep > maxEnabledStep) {
                return false; // Prevent navigation to disabled steps
            }

            // Dispatch Alpine event to trigger navigation
            window.dispatchEvent(new CustomEvent('navigate-to-step', {
                detail: { targetStep: targetStep }
            }));
        });
    });

    // Handle Save Draft button
    const saveDraftBtn = document.querySelector('a[href="#save-draft"]');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Dispatch Alpine event to trigger save
            window.dispatchEvent(new CustomEvent('save-draft'));
        });
    }
});
</script>

