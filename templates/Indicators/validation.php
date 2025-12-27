<?php
/**
 * @var \App\View\AppView $this
 * @var array $indicators
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\IndicatorsController $vcioConfig
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Validierung');
$this->assign('title', $title_for_layout);
?>

<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => $process->title,
    'size' => false,
    'weight' => false,
    'options' => ['class' => 'text-brand display-xs uppercase']
]) ?>
<p class="text-gray-600 mb-6"><?= __('VCIO Validierung') ?></p>

<div class="w-full mb-6">
    <?= $this->element('process_status', ['process' => $process]); ?>
</div>

<?= $this->element('molecules/primary_card', [
    'title' => __('Validierung der VCIO-Selbsteinstufung'),
    'subtitle' => __('VCIO-Validierung'),
    'body' => __('Für jede der sechs Qualitätsdimensionen des Qualitätsstandards, bezüglich deren Kriterien ein Schutzbedarf in der vorangegangenen Analyse festgestellt wurde, nehmen Sie nun eine Einstufung aus externer Sicht  vor und prüfen die geforderten Evidenzen des Prüflings.')
]) ?>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
<?php
    $ready = true;

    foreach ($vcioConfig as $qd_id => $qualityDimension):
        $quality_dimension_id = $qualityDimension['quality_dimension_id'];
        $url = ['action' => 'validate', $process->id, $qd_id];
        $qualityDimensionState = 'incomplete';
        $buttonText = __('Validieren');

        if(array_key_exists($quality_dimension_id, $indicators)) {
            $qualityDimensionState = 'complete';
            $buttonText = __('Bearbeiten');
        }

        echo $this->element('molecules/quality_dimension_card', [
            'title' => $qualityDimension['title'],
            'qd_id' => $qd_id,
            'icon' => $qualityDimension['icon'],
            'state' => $qualityDimensionState,
            'url' => $url,
            'buttonText' => $buttonText
        ]);
    endforeach; ?>

    <?php
        if(count($indicators) === count($vcioConfig)):
            echo $this->element('atoms/button', [
                'label' => __('Complete Validation'),
                'url' => ['controller' => 'Indicators', 'action' => 'completeValidation', $process->id],
                'variant' => 'primary',
                'size' => 'MD',
            ]);
        endif;
    ?>
</div>

