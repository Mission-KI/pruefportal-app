<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsData
 */

$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Validierung');
$this->assign('title', $title_for_layout);
?>

<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<p class="text-gray-600 mb-6"><?= __('VCIO-Validierung') ?></p>

<?= $this->element('process_status', ['process' => $process]); ?>

<?= $this->element('molecules/primary_card', [
    'title' => __('Ergebnis der VCIO Validierung'),
    'subtitle' => __('VCIO Validation'),
    'body' => __('Die VCIO Validierung zeigt die Bewertung der Qualitätsindikatoren durch den Prüfer. Die Einstufung erfolgt in den Kategorien A-D und wird mit der Erfüllung (ja/nein) bewertet.')
]) ?>

<?= $this->element('organisms/quality_dimensions_table', [
    'qualityDimensionsData' => $qualityDimensionsData,
    'showEditButtons' => false,
    'showIndicatorColumns' => true,
    'accordionMode' => false
]) ?>
