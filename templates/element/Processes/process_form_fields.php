<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Project $project
 * @var \App\Model\Entity\Process $process
 */
$process = $process ?? null;

echo $this->Form->control('project_id', [
    'type' => 'hidden',
    'value' => $project->id
]);
?>

<?= $this->element('atoms/heading', [
    'text' => __('Angaben zum Prüfprozess'),
    'level' => 'h3',
    'size' => 'lg',
    'weight' => 'regular',
    'color' => 'text-brand',
    'options' => ['class' => 'mb-4']
]) ?>

<div class="space-y-4">
    <?= $this->element('molecules/form_field', [
        'name' => 'title',
        'label' => __('Name des Prüfprozesses'),
        'type' => 'text',
        'required' => true,
        'tooltip' => __('Wählen Sie einen möglichst eindeutigen Namen, der es Prüfbeteiligten erleichtert, sich zu orientieren. Sie können auf der Projektseite jederzeit weitere Prüfprozesse hinzufügen.'),
        'atom_element' => 'atoms/form_input',
        'atom_data' => [
            'name' => 'title',
            'id' => 'process-title',
            'placeholder' => __('z.B. Bilderkennung'),
            'required' => true,
            'value' => $process ? $process->title : ''
        ]
    ]) ?>

    <?= $this->element('molecules/form_field', [
        'name' => 'description',
        'label' => __('Beschreibung des Prüfprozesses'),
        'type' => 'textarea',
        'tooltip' => __('Ergänzen Sie eine kurze aussagekräftige Beschreibung dafür, worum es in dem Prüfprozess geht.'),
        'help' => __('Ergänzen Sie eine kurze aussagekräftige Beschreibung dafür, worum es in dem Prüfprozess geht.'),
        'atom_element' => 'atoms/form_textarea',
        'atom_data' => [
            'name' => 'description',
            'id' => 'process-description',
            'placeholder' => __('Text'),
            'value' => $process ? $process->description : ''
        ]
    ]) ?>
</div>
