<?php

/**
 * Participant Form Rows Organism
 *
 * Static form rows for candidate and examiner (matching current backend logic)
 * Uses form_field molecule for proper styling and validation
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process|null $process
 * @var \App\Model\Entity\Project|null $project For projects/add form data persistence
 * @var string $mode Display mode: 'default' or 'inline' (hides info text/headings)
 * @var bool $showInitialRow Whether to show one empty row by default
 * @var string $roleFilter Filter to specific role (currently only 'examiner' supported)
 * @var bool $compact Use tighter spacing between form rows
 */
$process = $process ?? null;
$project = $project ?? null;
$mode = $mode ?? 'default';
$showInitialRow = $showInitialRow ?? false;
$roleFilter = $roleFilter ?? null;
$compact = $compact ?? false;
?>

<?php if ($mode === 'default'): ?>
<?= $this->element('atoms/heading', [
    'text' => __('Prozessbeteiligte'),
    'level' => 'h3',
    'size' => 'lg',
    'weight' => 'regular',
    'color' => 'text-brand',
    'options' => ['class' => 'mb-4']
]) ?>

<div class="mki-form-field-help text-gray-600 mt-2"><?= __('Ein Prüfprozess wird gemeinsam von Prüfling und PrüferIn durchgeführt. Der Prüfling ist für das Ausfüllen der Anwendungsfallbeschreibung und für die VCIO-Einstufung verantwortlich. Die/der PrüferIn ist für die Schutzbedarfsanalyse und die Validierung der Einstufung verantwortlich.') ?></div>

<?= $this->element('atoms/heading', [
            'text' => __('Prüfling'),
            'level' => 'h4',
            'size' => 'md',
            'weight' => 'medium',
            'color' => 'text-brand',
            'options' => ['class' => 'my-3']
        ]) ?>

<div class="mki-form-field-help text-gray-600 mt-2"><?= __('Als ProjekteigentümerIn sind Sie automatisch in der Rolle des Prüflings.') ?></div>
<?php endif; ?>

<div class="<?= $compact ? 'space-y-2' : 'space-y-3' ?>">

    <!-- Candidate Row (Hidden - auto-assigned to current user on backend) -->
    <input type="hidden" name="candidate_name" value="">
    <input type="hidden" name="candidate_email" value="">
    <input type="hidden" name="candidate_role" value="candidate">

    <!-- Dynamic Additional Participants Section -->
    <?php
    $initialParticipants = $showInitialRow ? "[{ name: '', role: 'examiner', email: '' }]" : "[]";
    ?>
    <div x-data="{ additionalParticipants: <?= $initialParticipants ?>, showInitialRow: <?= $showInitialRow ? 'true' : 'false' ?> }">
        <?php if ($mode === 'default'): ?>
        <?= $this->element('atoms/heading', [
            'text' => __('PrüferIn'),
            'level' => 'h4',
            'size' => 'md',
            'weight' => 'medium',
            'color' => 'text-brand',
            'options' => ['class' => 'mb-3']
        ]) ?>

        <div class="mki-form-field-help text-gray-600 mt-2">
            <?= __('Sie müssen zunächst keine/n PrüferIn  benennen. Erst wenn Sie einen gewissen Schutzbedarf festellen, kann es nötig werden, dass unabhängige PrüferInnen für die Validierung der VCIO-Einstufung zum Prozess hinzugefügt werden müssen.') ?>
            <br/><br/>
            <a href="https://docs.pruefportal.mission-ki.de/entries/4" target="_blank"><?= $this->element('atoms/icon', ['name' => 'external-link', 'size' => 'sm', 'options' => ['class' => 'w-5 h-5']]) ?><span class="underline"><?= __('Dokumentation') ?></span></a>
        </div>
        <?php endif; ?>


        <!-- Dynamic rows -->

        <template x-for="(participant, index) in additionalParticipants" :key="index">

            <div class="flex gap-4 align-center flex-wrap <?= $compact ? 'mt-2' : 'mt-4' ?>">
                <hr class="text-gray-100 w-full">
                <?= $this->element('molecules/form_field', [
                    'name' => 'additional_participant_name',
                    'type' => 'text',
                    'label' => __('Name'),
                    'atom_element' => 'atoms/form_input',
                    'atom_data' => [
                        'name' => 'additional_participants[][name]',
                        'id' => 'additional-participant-name',
                        'placeholder' => __('Text'),
                        'value' => '',
                        'attributes' => [
                            ':id' => '`additional-participant-name-${index}`',
                            ':name' => '`additional_participants[${index}][name]`',
                            'x-model' => 'participant.name',
                            'data-testid' => 'examiner-name-input'
                        ]
                    ],
                    'containerClass' => 'mb-0'
                ]) ?>

                <div class="mki-form-field-container mb-0" style="display:none">
                    <input type="hidden"
                        :id="`additional-participant-role-${index}`"
                        :name="`additional_participants[${index}][role]`"
                        value="examiner"
                        readonly>
                </div>
                <?= $this->element('molecules/form_field', [
                    'name' => 'additional_participant_email',
                    'type' => 'email',
                    'label' => __('E-Mail'),
                    'icon' => 'mail',
                    'help' => __('Personen die noch nicht registriert sind, erhalten automatisch eine Einladung.'),
                    'atom_element' => 'atoms/form_input',
                    'atom_data' => [
                        'name' => 'additional_participants[][email]',
                        'id' => 'additional-participant-email',
                        'type' => 'email',
                        'placeholder' => __('E-Mail-Adresse'),
                        'value' => '',
                        'attributes' => [
                            ':id' => '`additional-participant-email-${index}`',
                            ':name' => '`additional_participants[${index}][email]`',
                            'x-model' => 'participant.email',
                            'data-testid' => 'examiner-email-input'
                        ]
                    ],
                    'containerClass' => 'mb-0'
                ]) ?>

                <div class="mki-form-field-container mb-0" x-show="!(showInitialRow && index === 0)">
                    <label class="text-brand-deep font-normal text-md invisible mb-2" aria-hidden="true"><?= __('Actions') ?></label>
                    <?= $this->element('atoms/button', [
                        'icon' => 'trash-01',
                        'variant' => 'ghost',
                        'size' => 'sm',
                        'options' => [
                            'class' => 'hover:text-error-600 h-[44px] w-[44px]',
                            '@click' => 'additionalParticipants.splice(index, 1)',
                            'aria-label' => __('Person entfernen')
                        ]
                    ]) ?>
                </div>
            </div>
        </template>


        <!-- Add Person Button -->
        <div class="flex justify-end <?= $compact ? 'mt-2' : 'mt-4' ?>">
            <?= $this->element('atoms/button', [
                'label' => __('PrüferIn hinzufügen'),
                'icon' => 'plus',
                'iconPosition' => 'before',
                'variant' => 'secondary',
                'size' => 'sm',
                'type' => 'button',
                'options' => [
                    'class' => 'text-brand hover:text-brand-deep',
                    '@click' => "additionalParticipants.push({ name: '', role: '', email: '' })"
                ]
            ]) ?>
        </div>
    </div>
</div>
