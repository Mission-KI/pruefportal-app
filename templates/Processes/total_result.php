<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsConfig
 * @var array $ucd
 * @var array $qualityDimensionsData
 * @var array $qualityDimensionsSummary
 */
$this->assign('title', h($process->title) . ' - ' . __('vollständiger Prüfbericht'));
?>

<div class="container mx-auto py-8">
    <?= $this->element('process_status', ['process' => $process]); ?>
    <?= $this->element('organisms/total_result', [
        'process' => $process,
        'qualityDimensionsConfig' => $qualityDimensionsConfig,
        'ucd' => $ucd,
        'qualityDimensionsData' => $qualityDimensionsData,
        'qualityDimensionsSummary' => $qualityDimensionsSummary,
    ]) ?>
</div>
