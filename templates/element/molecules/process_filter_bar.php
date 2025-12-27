<?php
/**
 * Comment Filter Bar Molecule Component
 *
 * Process dropdown filter with optional Alpine.js change handler.
 *
 * @var \App\View\AppView $this
 * @var array $processes Array of process options [id => title]
 * @var int|null $process_id Currently selected process ID
 * @var string|null $redirect URL to redirect to
 * @var array|null $filterAction URL to filter action
 * @var string|null $onChange Alpine.js change handler (default: '' - no action)
 */

$processes = $processes ?? [];
$process_id = $process_id ?? null;
$onChange = $onChange ?? '';
?>

<div class="flex flex-col md:flex-row gap-4 items-start md:items-center">
    <?= $this->Form->create(null, [
        'url' => ['controller' => 'Processes', 'action' => 'filterProject'],
        'class' => 'flex flex-col md:flex-row gap-4 items-start md:items-center w-full',
        'x-data' => '',
        '@change' => $onChange
    ]) ?>

    <?= $this->Form->input('redirect', [
        'type' => 'hidden',
        'value' => $redirect
    ]) ?>

    <?= $this->element('molecules/form_field', [
        'type' => 'select',
        'name' => 'process_id',
        'containerClass' => 'w-full',
        'atom_element' => 'atoms/form_select',
        'atom_data' => [
            'id' => 'process-filter',
            'name' => 'process_id',
            'options' => $processes,
            'value' => $process_id,
            'label' => false,
        ]
    ]) ?>

    <?= $this->Form->end() ?>
</div>
