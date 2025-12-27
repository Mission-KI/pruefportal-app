<?php
/**
 * @var \App\View\AppView $this
 * @var array $indicators
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\IndicatorsController $vcioConfig
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Einstufung');
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
    'title' => __('Einstufung des KI-Systems nach der VCIO-Systematik'),
    'subtitle' => __('VCIO-Einstufung'),
    'body' => __('Für jede der Qualitätsdimensionen des Qualitätsstandards, bezüglich deren Kriterien ein Schutzbedarf in der vorangegangenen Analyse durch den/die PrüferIn festgestellt wurde, nehmen Sie nun eine Selbsteinstufung des KI-Systems vor und liefern bitte die geforderten Evidenzen, um diese Einstufung zu belegen.')
]) ?>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<?php
    $ready = true;

    foreach ($vcioConfig as $qd_id => $qualityDimension):
        $quality_dimension_id = $qualityDimension['quality_dimension_id'];
        $hasIndicators = array_key_exists($quality_dimension_id, $indicators);

        // Determine state and URL based on process status
        if ($process->status_id === 30) {
            // VCIO phase active - can edit everything
            if ($hasIndicators) {
                $state = 'complete';
                $url = ['action' => 'edit', $process->id, $qd_id];
                $buttonText = __('Einstufung bearbeiten');
            } else {
                $state = 'incomplete';
                $url = ['action' => 'add', $process->id, $qd_id];
                $buttonText = __('Einstufen');
                $ready = false;
            }
        } else {
            // VCIO complete (status >= 35) - read-only
            if ($hasIndicators) {
                $state = 'complete';
                $url = false;
                $buttonText = __('Abgeschlossen');
            } else {
                $state = 'incomplete';
                $url = false;
                $buttonText = __('Nicht ausgefüllt');
                $ready = false;
            }
        }

        echo $this->element('molecules/quality_dimension_card', [
            'title' => $qualityDimension['title'],
            'qd_id' => $qd_id,
            'icon' => $qualityDimension['icon'],
            'state' => $state,
            'url' => $url,
            'buttonText' => $buttonText
        ]);
    endforeach;
?>


</div>
<?php if ($ready): ?>
        <div class="py-4 w-full flex justify-end">
           <?= $this->element('atoms/button', [
                'label' => __('Einstufung überprüfen'),
                'url' => ['controller' => 'Indicators', 'action' => 'complete', $process->id],
                'variant' => 'primary',
                'size' => 'MD',
            ]); ?>
        </div>
<?php endif; ?>
