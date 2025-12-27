<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\AppController $currentLanguage
 * @var \App\Controller\CriteriaController $protectionNeedsAnalysis
 * @var \App\Controller\CriteriaController $criteria
 * @var \App\Controller\CriteriaController $AP_relevances
 * @var \App\Controller\CriteriaController $GF_relevances
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('Protection Needs Analysis');
$this->assign('title', $title_for_layout);
?>
<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => $process->title,
    'size' => false,
    'weight' => false,
    'options' => ['class' => 'text-brand display-xs mb-6']
]) ?>

<div class="w-full mb-6">
    <?= $this->element('process_status', ['process' => $process]); ?>
</div>

<?= $this->element('molecules/primary_card', [
    'title' => __('Schutzbedarfsanalyse'),
    'body' => __('Die Schutzbedarfsanalyse dient der Identifizierung und Bewertung von Qualitätsanforderungen für ein KI-System. Dabei werden die relevanten Qualitätsmerkmale für den jeweiligen Anwendungsfall ermittelt und ein Zielwert für deren Erreichung festgelegt.')
]) ?>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<?php
    $allComplete = true;

    foreach ($protectionNeedsAnalysis as $qd_id => $qualityDimension):
        // Use pre-calculated navigation state from controller (single source of truth)
        $qdState = $navigationState[$qd_id] ?? ['isComplete' => false, 'hasStarted' => false, 'url' => null];

        // Determine UI state and button text based on completion
        if ($qdState['isComplete']) {
            $state = 'complete';
            $buttonText = __('Bewertung bearbeiten');
        } elseif ($qdState['hasStarted']) {
            $state = 'current';
            $buttonText = __('Bewertung fortsetzen');
            $allComplete = false;
        } else {
            $state = 'incomplete';
            $buttonText = __('Bewerten');
            $allComplete = false;
        }

        echo $this->element('molecules/quality_dimension_card', [
            'title' => $qualityDimension['title'][$currentLanguage],
            'qd_id' => $qd_id,
            'icon' => $qualityDimension['icon'],
            'state' => $state,
            'url' => $qdState['url'],
            'buttonText' => $buttonText
        ]);
    endforeach;
?>

<?php
    if($allComplete) {
        echo $this->element('atoms/button', [
            'label' => __('Complete Rating'),
            'url' => ['controller' => 'Criteria', 'action' => 'complete', $process->id],
            'variant' => 'primary',
            'size' => 'MD',
        ]);
    }
?>
</div>

