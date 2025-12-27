<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsData
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO Self-assessment Results');
$this->assign('title', $title_for_layout);
?>

<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<p class="text-gray-600 mb-6"><?= __('VCIO Self-assessment Results') ?></p>

<?= $this->element('process_status', ['process' => $process]); ?>

<?= $this->element('molecules/primary_card', [
    'title' => __('Ergebnis der VCIO Selbsteinschätzung'),
    'subtitle' => __('VCIO Self-assessment'),
    'body' => __('Die VCIO Selbsteinschätzung zeigt die Bewertung der Qualitätsindikatoren für das KI-System. Die Einstufung erfolgt in den Kategorien A-D und wird mit der Erfüllung (ja/nein) bewertet.')
]) ?>

<?= $this->element('organisms/quality_dimensions_table', [
    'qualityDimensionsData' => $qualityDimensionsData,
    'showEditButtons' => false,
    'showIndicatorColumns' => true,
    'accordionMode' => false
]) ?>

<?php if ($process->status_id == 30): ?>
    <div class="py-4 w-full flex justify-end">
        <?= $this->element('atoms/button', [
            'label' => __('Einstufung abschließen'),
            'url' => ['controller' => 'Indicators', 'action' => 'complete', $process->id],
            'variant' => 'primary',
            'size' => 'MD',
        ]); ?>
    </div>
<?php endif; ?>
