<?php
/**
 * Process Step Completion Organism
 *
 * Encapsulates the standard pattern for process workflow completion pages.
 * Used when a user confirms completion of a step and triggers notification to the next party.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process Process entity
 * @var string $step_title Page title (e.g., "VCIO-Einstufung")
 * @var string $card_title Explanation card title
 * @var string $card_subtitle Subtitle shown above card title
 * @var string $card_body Main explanation text
 * @var string $checkbox_label Confirmation checkbox text
 * @var string $checkbox_description Help text below checkbox
 * @var array $form_action Form submission URL (e.g., ['action' => 'complete', $process->id])
 * @var string $button_label Submit button text (optional, default: "Accept and submit")
 * @var string $card_variant Card variant (optional, default: 'primary')
 * @var bool $show_progress_bar Whether to show progress bar (optional, default: true)
 * @var array $heading_options Heading element options (optional)
 */

$button_label = $button_label ?? __('Accept and submit');
$card_variant = $card_variant ?? 'primary';
$show_progress_bar = $show_progress_bar ?? true;
$heading_options = $heading_options ?? ['class' => 'text-primary display-xs uppercase'];
?>

<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => $process->title,
    'size' => false,
    'weight' => false,
    'options' => $heading_options
]) ?>

<div class="w-full mb-6">
    <?= $this->element('process_status', ['process' => $process]); ?>
</div>

<?= $this->element('molecules/card', [
    'title' => $card_title,
    'subtitle' => $card_subtitle,
    'subtitle_position' => 'above',
    'body' => $card_body,
    'variant' => $card_variant,
    'heading_level' => 'h4',
    'heading_size' => false,
    'heading_weight' => false,
    'options' => ['class' => 'mb-6']
]) ?>

<div x-data="{ finalConfirmation: false }">
    <?= $this->Form->create($process, ['class' => 'space-y-6 needs-validation', 'url' => $form_action]) ?>

        <div class="flex justify-center mb-8">
            <?= $this->element('atoms/form_checkbox', [
                'name' => 'finalConfirmation',
                'id' => 'checkFinishedCompletely',
                'label' => $checkbox_label,
                'description' => $checkbox_description,
                'checked' => false,
                'required' => true,
                'attributes' => [
                    'x-model' => 'finalConfirmation'
                ]
            ]) ?>
        </div>

        <div class="flex justify-end items-center">
            <?= $this->element('atoms/button', [
                'label' => $button_label,
                'variant' => 'primary',
                'size' => 'MD',
                'options' => [
                    'type' => 'submit',
                    ':disabled' => '!finalConfirmation'
                ]
            ]) ?>
        </div>

    <?= $this->Form->end() ?>
</div>
