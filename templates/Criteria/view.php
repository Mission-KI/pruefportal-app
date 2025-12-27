<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsData
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('Schutzbedarfsanalyse');
$this->assign('title', $title_for_layout);
?>

<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<p class="text-gray-600 mb-6"><?= __('Schutzbedarfsanalyse') ?></p>

<?= $this->element('process_status', ['process' => $process]); ?>

<?= $this->element('molecules/primary_card', [
    'title' => __('Ergebnis der Schutzbedarfsanalyse'),
    'subtitle' => __('Schutzbedarfsanalyse'),
    'body' => __('Die Schutzbedarfsanalyse dient der Identifizierung und Bewertung von Qualitätsanforderungen für ein KI-System. Dabei werden die relevanten Qualitätsmerkmale für den jeweiligen Anwendungsfall ermittelt und ein Zielwert für deren Erreichung festgelegt.'),
]) ?>

<?= $this->element('organisms/quality_dimensions_table', [
    'qualityDimensionsData' => $qualityDimensionsData,
    'showEditButtons' => false,
    'showIndicatorColumns' => false,
    'accordionMode' => false
]) ?>
