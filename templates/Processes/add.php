<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var \App\Model\Entity\Project $project
 */
$this->assign('title', __('New Process'));
$this->assign('reserve_sidebar_space', 'true');
?>

<?= $this->element('molecules/primary_card', [
    'variant' => 'primary',
    'subtitle' => __('Projekte'),
    'title' => __('Neuen Prüfprozess anlegen'),
    'body' => __('Die Grundlage eines Prüfprozesses ist das Projekt, dem es zugeordnet ist. Jeder Prüfprozess benötigt einen Prüfling und mindestens einen Prüfer.')
]) ?>

<?= $this->Form->create($process, [
    'class' => 'space-y-8',
    'novalidate' => true,
    'x-data' => '{ formValid: false }',
    'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
    '@input' => 'formValid = $el.checkValidity()'
]) ?>

    <?= $this->element('Processes/process_form_fields', ['project' => $project, 'process' => $process]) ?>

    <?= $this->element('organisms/participant_form_rows', ['process' => $process]) ?>

    <div class="flex gap-4 justify-between">
        <?= $this->element('atoms/button', [
            'label' => __('Abbrechen'),
            'variant' => 'secondary',
            'size' => 'md',
            'url' => ['controller' => 'Projects', 'action' => 'view', $project->id]
        ]) ?>

        <?= $this->element('atoms/button', [
            'label' => __('Prüfprozess anlegen'),
            'variant' => 'primary',
            'size' => 'md',
            'type' => 'submit',
            'options' => [
                ':disabled' => '!formValid'
            ]
        ]) ?>
    </div>

<?= $this->Form->end() ?>
