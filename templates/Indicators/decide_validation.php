<?php
/**
 * Validation Decision Screen (Status 35)
 *
 * @var \App\View\AppView $this
 * @var object $process Process entity with project and examiners
 * @var string|null $riskLevel 'high', 'moderate', 'low', or null
 * @var bool $hasExaminer Whether process has at least one examiner
 * @var bool $qualificationConfirmed Whether examiner qualification is confirmed (high risk only)
 */

$this->assign('title', __('Validierungsentscheidung'));
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <?= $this->element('process_status', ['process' => $process]); ?>

       <?= $this->element('molecules/primary_card', [
            'title' => __('VCIO-Einstufung validieren'),
            'subtitle' => $process->title,
            'body' => __('Für Anwendungsfälle mit geringem bis moderatem Schutzbedarf ist die Validierung der VCIO-Selbsteinstufung optional. Für Anwendungsfälle mit hohem Schutzbedarf ist eine Validierung durch eine/n qualifizierten PrüferIn erforderlich.')
        ]) ?>

    <!-- Risk Level Section -->
    <?php
    // Risk badge configuration
    $cardVariants = [
        'high' => 'danger',
        'moderate' => 'warning',
        'low' => 'success'
    ];
    $cardVariant = $cardVariants[$riskLevel] ?? 'default';

    $iconNames = [
        'high' => 'alert-triangle',
        'moderate' => 'alert-square',
        'low' => 'check-circle'
    ];
    $iconName = $iconNames[$riskLevel] ?? 'alert-triangle';

    $icon = $this->element('atoms/icon', [
        'name' => $iconName,
        'size' => 'sm',
        'options' => ['class' => 'mr-1.5 inline-flex']
    ]);

    $riskLabels = [
        'high' => __('Hoher Schutzbedarf'),
        'moderate' => __('Moderater Schutzbedarf'),
        'low' => __('Niedriger Schutzbedarf')
    ];
    $riskLabel = $riskLabels[$riskLevel] ?? __('Unbekannter Schutzbedarf');

    $riskBadge = $this->element('atoms/badge', [
        'text' => $icon . $riskLabel,
        'variant' => $cardVariant,
        'size' => 'lg',
        'escape' => false
    ]);

    // Risk explanation texts
    $riskExplanations = [
        'high' => __('Aufgrund des hohen Schutzbedarfs ist eine Validierung durch eine/n qualifizierte/n PrüferIn verpflichtend. {0}. Um fortzufahren, müssen Sie eine/n PrüferIn hinzufügen und dessen Qualifikation bestätigen.',
            $this->Html->link(
                $this->element('atoms/icon', [
                    'name' => 'external-link',
                    'size' => 'sm',
                    'options' => ['class' => 'inline-block mr-1']
                ]) . __('Weitere Informationen'),
                'https://docs.pruefportal.mission-ki.de/entries/4',
                [
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'class' => 'text-blue-600 hover:text-blue-800 underline',
                    'escape' => false
                ]
            )
        ),
        'moderate' => __('Validierung ist optional aber empfohlen {0}.',
            $this->Html->link(
                $this->element('atoms/icon', [
                    'name' => 'external-link',
                    'size' => 'sm',
                    'options' => ['class' => 'inline-block mr-1']
                ]) . __('Weitere Informationen'),
                'https://docs.pruefportal.mission-ki.de/entries/4',
                [
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'class' => 'text-blue-600 hover:text-blue-800 underline',
                    'escape' => false
                ]
            )
        ),
        'low' => __('Validierung ist optional {0}.',
            $this->Html->link(
                $this->element('atoms/icon', [
                    'name' => 'external-link',
                    'size' => 'sm',
                    'options' => ['class' => 'inline-block mr-1']
                ]) . __('Weitere Informationen'),
                'https://docs.pruefportal.mission-ki.de/entries/4',
                [
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'class' => 'text-blue-600 hover:text-blue-800 underline',
                    'escape' => false
                ]
            )
        )
    ];
    $riskExplanation = $riskExplanations[$riskLevel] ?? __('Der Schutzbedarf für diesen Prozess konnte nicht ermittelt werden.');
    ?>

    <!-- PNA paragraph with inline risk badge -->
    <div class="mb-6">
        <p class="text-gray-700">
            <?php
            $pnaLink = $this->Html->link(
                $this->element('atoms/icon', [
                    'name' => 'arrow-right',
                    'size' => 'sm',
                    'options' => ['class' => 'inline-block mr-1']
                ]) . __('Schutzbedarfsanalyse'),
                ['controller' => 'Criteria', 'action' => 'view', $process->id],
                ['class' => 'text-blue-600 hover:text-blue-800 underline', 'escape' => false]
            );
            ?>
            <?= __('Für den Prozess <strong>{0}</strong> im Projekt <strong>{1}</strong> wurde in der {2} ein {3} ermittelt.',
                h($process->title),
                h($process->project->title),
                $pnaLink,
                '<span class="flex w-full justify-center py-2">'.$riskBadge.'</span>'

            ) ?>
        </p>
    </div>

    <!-- Risk info card -->
    <?php
    $cardTitle = $this->element('atoms/icon', [
        'name' => 'info-circle',
        'size' => 'sm',
        'options' => ['class' => 'inline-block mr-2']
    ]) . __('Validierungsanforderung');
    ?>
    <?= $this->element('molecules/card', [
        'title' => $cardTitle,
        'body' => $riskExplanation,
        'variant' => 'default',
        'escape' => false,
        'options' => [
            'class' => 'my-12 bg-gray-100'
        ]
    ]) ?>


    <?php if ($riskLevel === 'high'): ?>
        <!-- HIGH RISK SCENARIOS -->
        <div class="space-y-6" x-data="{ qualificationConfirmed: false, examinerInvited: false }">

            <?php if (!$hasExaminer): ?>
                <!-- SCENARIO 1: High Risk + No Examiner -->
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'confirmQualification', $process->id]
                ]) ?>

                <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-6">
                                    <?= $this->element('atoms/icon', ['name' => 'user-add', 'size' => 'md']) ?>
                    <?= __('PrüferIn einladen und Qualifikation bestätigen') ?>
                </h3>

                <p class="text-md text-gray-600">
                    <?= __('Diesem Prozess ist noch kein/e PrüferIn zugeordnet. Bitte benennen Sie wenigstens eine Person, die über die für die Validierung erforderlichen Qualifikationen verfügt.') ?>
                </p>

                <!-- Participant form rows (invite examiner) -->
                <div class="mb-6">
                    <?= $this->element('organisms/participant_form_rows', [
                        'process' => $process,
                        'mode' => 'inline',
                        'showInitialRow' => true,
                        'roleFilter' => 'examiner',
                        'compact' => true
                    ]) ?>
                </div>

                <!-- Qualification confirmation -->
                <div class="mb-6">
                    <?php
                    $qualificationLink = $this->Html->link(
                        $this->element('atoms/icon', [
                            'name' => 'external-link',
                            'size' => 'sm',
                            'options' => ['class' => 'inline-block mr-1']
                        ]) . __('erforderlichen Qualifikationen'),
                        'https://docs.pruefportal.mission-ki.de/entries/4',
                        [
                            'target' => '_blank',
                            'rel' => 'noopener noreferrer',
                            'class' => 'text-blue-600 hover:text-blue-800 underline',
                            'escape' => false
                        ]
                    );
                    ?>
                    <?= $this->element('molecules/disclaimer', [
                        'type' => 'checkbox',
                        'name' => 'confirm_qualification',
                        'id' => 'confirm-qualification',
                        'title' => __('Qualifikation bestätigen'),
                        'text' => __('Ich bestätige, dass der/die PrüferIn die {0} besitzt/besitzen.', $qualificationLink),
                        'required' => true,
                        'textSize' => 'text-lg',
                        'escapeDescription' => false,
                        'attributes' => [
                            'x-model' => 'qualificationConfirmed'
                        ]
                    ]) ?>
                </div>

                <div class="mt-6">
                    <?= $this->element('atoms/button', [
                        'label' => __('PrüferIn einladen und Qualifikation bestätigen'),
                        'variant' => 'primary',
                        'type' => 'submit',
                        'options' => [
                            'x-bind:disabled' => '!qualificationConfirmed'
                        ]
                    ]) ?>
                </div>

                <?= $this->Form->end() ?>

            <?php else: ?>
                <!-- SCENARIO 2: High Risk + Examiner Assigned -->
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'confirmQualification', $process->id]
                ]) ?>

                <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-6">
                    <?= $this->element('atoms/icon', ['name' => 'user-check', 'size' => 'md']) ?>
                    <?= __('Qualifikation bestätigen') ?>
                </h3>

                <!-- Assigned Examiners -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-brand-deep mb-3">
                        <?= __('Zugewiesene PrüferIn:') ?>
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($process->examiners as $examiner): ?>
                            <?php
                            $nameParts = explode(' ', $examiner->full_name);
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                            ?>
                            <?= $this->element('molecules/user_badge', [
                                'avatar_initials' => $initials,
                                'full_name' => $examiner->salutation_name,
                                'role' => __('PrüferIn')
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Qualification confirmation -->
                <div class="mb-6">
                    <?php
                    $qualificationLink = $this->Html->link(
                        $this->element('atoms/icon', [
                            'name' => 'external-link',
                            'size' => 'sm',
                            'options' => ['class' => 'inline-block mr-1']
                        ]) . __('erforderlichen Qualifikationen'),
                        'https://docs.pruefportal.mission-ki.de/entries/4',
                        [
                            'target' => '_blank',
                            'rel' => 'noopener noreferrer',
                            'class' => 'text-blue-600 hover:text-blue-800 underline',
                            'escape' => false
                        ]
                    );
                    ?>
                    <?= $this->element('molecules/disclaimer', [
                        'type' => 'checkbox',
                        'name' => 'confirm_qualification',
                        'id' => 'confirm-qualification',
                        'title' => __('Qualifikation bestätigen'),
                        'text' => __('Ich bestätige, dass die PrüferIn die {0} besitzen.', $qualificationLink),
                        'required' => true,
                        'textSize' => 'text-base',
                        'escapeDescription' => false,
                        'attributes' => [
                            'x-model' => 'qualificationConfirmed'
                        ]
                    ]) ?>
                </div>

                <div class="mt-6">
                    <?= $this->element('atoms/button', [
                        'label' => __('Qualifikation bestätigen und zur Validierung übergehen'),
                        'variant' => 'primary',
                        'type' => 'submit',
                        'options' => [
                            'x-bind:disabled' => '!qualificationConfirmed'
                        ]
                    ]) ?>
                </div>

                <?= $this->Form->end() ?>

            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- MODERATE/LOW RISK SCENARIOS -->
        <div class="space-y-6">

            <?php if (!$hasExaminer): ?>
                <!-- SCENARIO 3: Moderate/Low Risk + No Examiner (Skip Validation) -->
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'skipValidation', $process->id]
                ]) ?>


                <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-6">
                    <?= __('Validierung überspringen') ?>
                </h3>

                <p class="text-gray-700 mb-6">
                    <?= __('Schließen Sie den Prozess ohne externe Validierung ab. Die Selbsteinstufung wird als Endergebnis verwendet.') ?>
                </p>

                <div>
                    <?= $this->element('atoms/button', [
                        'label' => __('Ohne Validierung abschließen'),
                        'variant' => 'secondary',
                        'type' => 'submit',
                        'options' => [
                            'data-confirm' => __('Prozess ohne Validierung abschließen?')
                        ]
                    ]) ?>
                </div>

                <?= $this->Form->end() ?>

            <?php else: ?>
                <!-- SCENARIO 4: Moderate/Low Risk + Examiner Assigned -->
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'proceedWithValidation', $process->id]
                ]) ?>

                <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-6">
                    <?= __('Zur Validierung übergehen') ?>
                </h3>

                <!-- Assigned Examiners -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-brand-deep mb-3">
                        <?= __('Zugewiesene PrüferInnen:') ?>
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($process->examiners as $examiner): ?>
                            <?php
                            $nameParts = explode(' ', $examiner->full_name);
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                            ?>
                            <?= $this->element('molecules/user_badge', [
                                'avatar_initials' => $initials,
                                'full_name' => $examiner->salutation_name,
                                'role' => __('PrüferIn')
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">
                    <?= __('Der Prozess wird an die zugewiesenen PrüferInnen zur Validierung übergeben.') ?>
                </p>

                <div>
                    <?= $this->element('atoms/button', [
                        'label' => __('Zur Validierung übergehen'),
                        'variant' => 'primary',
                        'type' => 'submit',
                        'options' => [
                            'data-confirm' => __('Prozess an PrüferIn übergeben?')
                        ]
                    ]) ?>
                </div>

                <?= $this->Form->end() ?>

            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>
