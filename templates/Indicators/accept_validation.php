<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsData
 */

$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Validierung bestätigen');
$this->assign('title', $title_for_layout);
?>

<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<p class="text-gray-600 mb-6"><?= __('VCIO-Validierung bestätigen') ?></p>

<?= $this->element('process_status', ['process' => $process]); ?>

<?= $this->element('molecules/primary_card', [
    'title' => __('Validierung bestätigen'),
    'subtitle' => __('VCIO-Einstufung'),
    'body' => __('Die Prüfung und Bewertung Ihrer Selbsteinschätzung seitens der prüfenden Person ist nun abgeschlossen. Sie haben nun Gelegenheit, das Ergebnis in der Gesamtdarstellung nachzuvollziehen. Wenn Sie dieses Ergebnis bestätigen, wird der eigentliche Prüfbericht sowie die Gesamtbewertung erstellt.')
]) ?>

<?= $this->element('organisms/quality_dimensions_table', [
    'qualityDimensionsData' => $qualityDimensionsData,
    'showEditButtons' => false,
    'showIndicatorColumns' => true,
    'accordionMode' => false
]) ?>

<?= $this->Form->create(null, [
    'url' => ['action' => 'acceptValidation', $process->id]
]) ?>
<div class="final-confirmation mt-8 bg-white rounded-lg shadow p-6" x-data="{ finalConfirmation: false }">
    <div class="mb-4">
        <?= $this->element('atoms/form_checkbox', [
            'name' => 'final_confirmation',
            'label' => __('Ich habe die Validierung der Selbsteinschätzung zur Kenntnis genommen und bestätige diese.'),
            'attributes' => [
                'x-model' => 'finalConfirmation'
            ]
        ]) ?>
    </div>

    <p class="text-gray-600 mb-6">
        <?= __('Bei Unklarheiten wenden Sie sich bitte direkt an den/die PrüferIn.') ?>
    </p>

    <?= $this->Form->button(__('Validierung bestätigen'), [
        'type' => 'submit',
        'class' => 'w-full bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
        'x-bind:disabled' => '!finalConfirmation'
    ]) ?>
</div>
<?= $this->Form->end() ?>
