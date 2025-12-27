<?php
/**
 * Overall Assessment Organism
 *
 * Complete assessment report for a process showing:
 * - Process metadata header
 * - Use case purpose and AI tasks
 * - Assessment cards for each quality dimension
 * - Footer with process ID and actions
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process Process entity with relations (required)
 * @var array $qualityDimensionsConfig Quality dimensions config from ProtectionNeedsAnalysis.json (required)
 * @var array $assessmentResults Assessment results grouped by quality_dimension_id (required)
 *   Example: [
 *     10 => ['rating' => 2, 'protection_needs' => 1, 'passed' => true],
 *     20 => ['rating' => 3, 'protection_needs' => 2, 'passed' => true],
 *     ...
 *   ]
 * @var array $options Additional HTML attributes
 */

use Cake\I18n\FrozenTime;

// Set defaults
$process = $process ?? null;
$qualityDimensionsConfig = $qualityDimensionsConfig ?? [];
$assessmentResults = $assessmentResults ?? [];
$options = $options ?? [];

// Validate required fields
if (!$process) {
    if (\Cake\Core\Configure::read('debug')) {
        echo '<span class="text-red-500">[OverallAssessment: process required]</span>';
    }
    return;
}

if (empty($qualityDimensionsConfig)) {
    if (\Cake\Core\Configure::read('debug')) {
        echo '<span class="text-red-500">[OverallAssessment: qualityDimensionsConfig required]</span>';
    }
    return;
}

// Build container classes
$containerClasses = [
    'overall-assessment',
    'bg-primary',
    'text-white',
    'rounded-lg',
    'p-8',
    'space-y-8',
];

if (isset($options['class'])) {
    $containerClasses[] = $options['class'];
    unset($options['class']);
}
$options['class'] = implode(' ', $containerClasses);

// Extract data from process
$project = $process->project ?? null;
$usecaseDescription = isset($process->usecase_descriptions[0]) ? $process->usecase_descriptions[0] : null;
$candidate = $process->candidate ?? null;

// Map quality dimension IDs to abbreviations
$qualityDimensionMap = [
    10 => 'CY',
    20 => 'TR',
    30 => 'ND',
    40 => 'VE',
    50 => 'DA',
    60 => 'MA'
];

// Parse usecase description JSON if available
$usecasePurpose = '';
$usecaseTasks = '';
if ($usecaseDescription && !empty($usecaseDescription->description)) {
    $descriptionData = json_decode($usecaseDescription->description, true);
    if ($descriptionData && is_array($descriptionData)) {
        // Find step 1 (purpose) and step 2 (tasks)
        foreach ($descriptionData as $step) {
            if (isset($step['step'])) {
                if ($step['step'] === 1 && isset($step['value'])) {
                    $usecasePurpose = $step['value'];
                }
                if ($step['step'] === 2 && isset($step['value'])) {
                    $usecaseTasks = $step['value'];
                }
            }
        }
    }
}
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <!-- MISSION KI Branding -->
    <div class="mb-6">
        <?= $this->Html->image('pruefportal_logo2_compact.svg', [
            'alt' => 'MISSION KI Prüfportal Beta',
            'class' => 'h-12 w-auto',
            'style' => 'filter: brightness(0) invert(1);'
        ]) ?>
    </div>

    <!-- Main Title -->
    <h4 class="mb-8">
        Bewertung der KI-Anwendung "<?= h($process->title) ?>"
    </h4>


    <!-- Metadata Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 py-8 mb-8 text-center text-md b border-y-2 border-brand-light">

        <div>
            <div class="text-yellow-300  font-semibold mb-1">Branche</div>
            <div class="text-white">
                <?php if ($project && !empty($project->industry)): ?>
                    <?= h($project->industry) ?>
                <?php else: ?>
                    <span class="text-white/60">-</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Datum der Prüfung -->
        <div>
            <div class="text-yellow-300 font-semibold mb-1">Datum der Prüfung</div>
            <div class="text-white">
                <?php if ($process->modified): ?>
                    <?= $process->modified->format('d.m.Y') ?>
                <?php else: ?>
                    <span class="text-white/60">-</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rolle -->
        <div>
            <div class="text-yellow-300 font-semibold mb-1">Rolle</div>
            <div class="text-white">
                <?php if ($candidate): ?>
                    Entwickler,<br>Betreiber
                <?php else: ?>
                    <span class="text-white/60">-</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Betriebsort -->
        <div>
            <div class="text-yellow-300 font-semibold mb-1">Betriebsort</div>
            <div class="text-white">
                <?php if ($project && !empty($project->location)): ?>
                    <?= h($project->location) ?>
                <?php else: ?>
                    <span class="text-white/60">-</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kontakt -->
        <div>
            <div class="text-yellow-300  font-semibold mb-1">Kontakt</div>
            <div class="text-white">
                <?php if ($candidate): ?>
                    <?= h($candidate->full_name) ?><br>
                    <?php if (!empty($candidate->email)): ?>
                        <a href="mailto:<?= h($candidate->email) ?>" class="text-white/80 hover:text-white underline">
                            <?= h($candidate->email) ?>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-white/60">-</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Prüfungsart -->
        <div>
            <div class="text-yellow-300 font-semibold mb-1">Prüfungsart</div>
            <div class="text-white">
                Erstprüfung
            </div>
        </div>
    </div>


    <!-- Purpose and Tasks Section -->
    <div class="grid md:grid-cols-2 gap-8 mb-8 text-md">
        <!-- Zweck der Anwendung -->
        <div>
            <h2 class="text-yellow-300 text-lg font-semibold mb-3">Zweck der Anwendung</h2>
            <div class="leading-relaxed">
                <?php if (!empty($usecasePurpose)): ?>
                    <?= nl2br(h($usecasePurpose)) ?>
                <?php else: ?>
                    <span class="text-white/60 italic">Nicht angegeben</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Aufgaben der KI -->
        <div>
            <h2 class="text-yellow-300 text-lg font-semibold mb-3">Aufgaben der KI</h2>
            <div class="leading-relaxed">
                <?php if (!empty($usecaseTasks)): ?>
                    <?= nl2br(h($usecaseTasks)) ?>
                <?php else: ?>
                    <span class="text-white/60 italic">Nicht angegeben</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Assessment Cards Grid -->
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($qualityDimensionsConfig as $abbr => $dimension): ?>
            <?php
                $qualityDimensionId = $dimension['quality_dimension_id'];
                $result = $assessmentResults[$qualityDimensionId] ?? null;

                // Determine if criterion was rated
                $notRated = !$result;
                $passed = $result['passed'] ?? false;
                $rating = $result['rating'] ?? 0;
                $protectionNeeds = $result['protection_needs'] ?? 0;

                // Get title (remove "Qualitätsdimension " prefix)
                $title = str_replace('Qualitätsdimension ', '', $dimension['title']['de']);
            ?>
            <?= $this->element('molecules/criterion_assessment_card', [
                'title' => $title,
                'abbreviation' => $abbr,
                'icon' => $dimension['icon'],
                'passed' => $passed,
                'rating' => $rating,
                'protection_needs' => $protectionNeeds,
                'not_rated' => $notRated
            ]) ?>
        <?php endforeach; ?>
    </div>

    <!-- Footer Section -->
    <div class="flex flex-col items-center gap-6 pt-6 border-t-2 border-brand-light">
        <!-- Process ID -->
        <div class="text-center text-sm  w-full">
            Prüf-ID: <?= h($process->id) ?>
        </div>


        <div class="flex justify-between w-full">
            <!-- Actions -->
            <!-- Download Button -->
            <?= $this->element('atoms/button', [
                'label' => __('vollständiger Prüfbericht'),
                'icon' => 'download-01',
                'iconPosition' => 'before',
                'variant' => 'ghost',
                'size' => 'MD',
                'url' => ['controller' => 'Processes', 'action' => 'download', $process->id],
                'options' => ['class' => 'border border-white text-white hover:bg-white/10']
            ]) ?>

            <!-- QR Code Placeholder -->
            <div class="w-16 h-16 bg-white rounded flex items-center justify-center">
                <div class="text-purple-900 text-xs font-bold text-center">QR</div>
            </div>
        </div>
    </div>


</div>

<?= $this->element('molecules/disclaimer', [
            'type' => 'card',
            'options' => [
                'class' => 'mt-10'
            ]
        ]) ?>
