<?php
/**
 * Overall Assessment Demo View
 *
 * Route: /projects/overall-assessment
 */
?>

<div class="container mx-auto py-8">
    <?= $this->element('organisms/overall_assessment', [
        'process' => $mockProcess,
        'qualityDimensionsConfig' => $qualityDimensionsConfig,
        'assessmentResults' => $assessmentResults
    ]) ?>
</div>
