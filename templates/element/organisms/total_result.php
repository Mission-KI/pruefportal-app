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
 * @var array $ucd Usecase Description
 * @var array $qualityDimensionsConfig Quality dimensions config from ProtectionNeedsAnalysis.json (required)
 * @var array $qualityDimensionsData Normalized data for quality dimensions table
 * @var array $qualityDimensionsSummary Pre-calculated summary per quality dimension
 * @var array $options Additional HTML attributes
 */

use Cake\I18n\FrozenTime;

$process = $process ?? null;
$qualityDimensionsConfig = $qualityDimensionsConfig ?? [];
$qualityDimensionsData = $qualityDimensionsData ?? [];
$qualityDimensionsSummary = $qualityDimensionsSummary ?? [];
$options = $options ?? [];
$applicationPurpose = array_key_exists('UC_1-2', $ucd) ? $ucd['UC_1-2'] : 'UC_1-2';
$applicationTasks = array_key_exists('UC_1-3', $ucd) ? $ucd['UC_1-3'] : 'UC_1-3';

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
$options['data-testid'] = 'overall-assessment';
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <!-- MISSION KI Branding -->
    <div class="mb-6">
        <?= $this->Html->image('pruefportal_logo2_compact.svg', [
            'alt' => 'MISSION KI Prüfportal Beta',
            'class' => 'h-12 w-auto',
            'style' => 'filter: brightness(0) invert(1);',
            'fullBase' => true
        ]) ?>
    </div>

    <!-- Main Title -->
    <h4 class="mb-8">
        <?= __('Bewertung der KI-Anwendung') ?> &quot;<?= h($process->title) ?>&quot;
    </h4>

    <!-- Metadata Grid -->
    <div class="grid md:grid-cols-2 gap-8 mb-8 text-md b py-8 border-y-2 border-brand-light">

        <!-- Datum der Prüfung -->
        <div>
            <div class="text-yellow-300 font-semibold mb-1"><?= __('Datum der Prüfung') ?></div>
            <div class="text-white">
                <?= $process->modified->format('d.m.Y') ?>
            </div>
        </div>

        <!-- Kontakt -->
        <div>
            <div class="text-yellow-300  font-semibold mb-1"><?= __('Kontakt') ?></div>
            <div class="text-white">
                <?php if ($process->has('project') && $process->project->has('user')): ?>
                    <?= h($process->project->user->full_name) ?><br>
                    <?php if (!empty($process->project->user->username)): ?>
                        <a href="mailto:<?= h($process->project->user->username) ?>" class="text-white/80 hover:text-white underline">
                            <?= h($process->project->user->username) ?>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-white/60">-</span>
                <?php endif; ?>
            </div>
        </div>

    </div>


    <!-- Purpose and Tasks Section -->
    <div class="grid md:grid-cols-2 gap-8 mb-8 text-md">
        <!-- Zweck der Anwendung -->
        <div>
            <h2 class="text-yellow-300 text-lg font-semibold mb-3"><?= __('Zweck der Anwendung') ?></h2>
            <div class="leading-relaxed">
                <?= $this->Text->autoParagraph($applicationPurpose) ?>
            </div>
        </div>

        <!-- Aufgaben der KI -->
        <div>
            <h2 class="text-yellow-300 text-lg font-semibold mb-3"><?= __('Aufgaben der KI') ?></h2>
            <div class="leading-relaxed">
                <?= $this->Text->autoParagraph($applicationTasks) ?>
            </div>
        </div>
    </div>

    <!-- Assessment Cards Grid -->
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($qualityDimensionsConfig as $qualityDimension => $dimension): ?>
            <?php
            $summary = $qualityDimensionsSummary[$qualityDimension] ?? [];
            $protectionNeeds = $summary['protectionNeeds'] ?? 0;
            $rating = $summary['rating'] ?? 0;
            $notRated = $summary['notRated'] ?? true;
            $passed = $summary['passed'] ?? false;
            ?>
            <?= $this->element('molecules/criterion_assessment_card', [
                'title' => $dimension['title'],
                'abbreviation' => $qualityDimension,
                'icon' => $dimension['icon'],
                'passed' => $passed,
                'protection_needs' => $protectionNeeds,
                'rating' => $rating,
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

        <div class="flex justify-between w-full print-hidden">
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
        </div>
    </div>


</div>

<?= $this->element('molecules/disclaimer', [
    'type' => 'card',
    'options' => [
        'class' => 'mt-10'
    ]
]) ?>
