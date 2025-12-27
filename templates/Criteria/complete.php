<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsData
 */

$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('Schutzbedarf-Analyse abschließen');
$this->assign('title', $title_for_layout);
?>

<div class="complete-wrapper max-w-4xl mx-auto py-8">
    <!-- 1. Intro Card -->
    <?= $this->element('molecules/primary_card', [
        'title' => __('Schutzbedarf-Analyse abschließen'),
        'subtitle' => $process->title,
        'body' => __('Bitte überprüfen Sie Ihre Angaben vor dem Abschluss der Analyse. Sie können einzelne Qualitätsdimensionen bei Bedarf noch bearbeiten.')
    ]) ?>

    <!-- 2. Criteria Review Section -->
    <div class="criteria-review-section mt-8">
        <?= $this->element('organisms/quality_dimensions_table', [
            'qualityDimensionsData' => $qualityDimensionsData,
            'showEditButtons' => true,
            'showIndicatorColumns' => false,
            'accordionMode' => true
        ]) ?>
    </div>

    <!-- 3. Final Confirmation -->
    <?= $this->Form->create(null, ['url' => ['action' => 'complete', $process->id]]) ?>
    <div class="final-confirmation mt-8 bg-white rounded-lg shadow p-6" x-data="{ finalConfirmation: false }">
        <div class="mb-4">
            <?= $this->element('atoms/form_checkbox', [
                'name' => 'final_confirmation',
                'label' => __('Die Analyse ist vollständig und korrekt ausgefüllt'),
                'attributes' => [
                    'x-model' => 'finalConfirmation'
                ]
            ]) ?>
        </div>

        <p class="text-gray-600 mb-6">
            <?= __('Der Prüfer wird sich nach Abschluss der Prüfung bei Ihnen melden.') ?>
        </p>

        <?= $this->Form->button(__('Analyse abschließen'), [
            'type' => 'submit',
            'class' => 'w-full bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
            'x-bind:disabled' => '!finalConfirmation'
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
