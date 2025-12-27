<?php
/**
 * Process Cards List - Shared Element
 *
 * Renders a grid of process cards for either candidate or examiner view.
 * All business logic is handled by ProcessesCell enrichment.
 *
 * @var \App\View\AppView $this
 * @var array $processes Collection of process entities (already enriched by Cell)
 * @var array $statuses Status configuration array
 * @var array $steps Steps mapping array
 */
?>

<div class="flex flex-wrap gap-4">
    <?php foreach ($processes as $process): ?>
        <?php
        $actions = [];

        // Primary action from Cell enrichment
        if ($process->continue_action) {
            $actions[] = array_merge($process->continue_action, [
                'options' => ['class' => 'w-full md:w-auto']
            ]);
        }

        // Status label
        $statusLabel = __('In Bearbeitung');
        if ($process->status_id === 40) {
            $statusLabel = __('Wartet auf Prüfer');
        }
        if ($process->status_id === 60) {
            $statusLabel = __('Fertig');
        }
    ?>

        <?= $this->element('organisms/process_card', [
            'process' => $process,
            'statuses' => $statuses,
            'steps' => $steps,
            'actions' => $actions,
            'status_label' => $statusLabel
        ]) ?>
    <?php endforeach; ?>
</div>
