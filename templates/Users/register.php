<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', __('Register'));

// Build form content
ob_start();
?>

<?= $this->Form->create($user, [
    'class' => 'space-y-6',
    'x-data' => '{ formValid: false }',
    'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
    '@input' => 'formValid = $el.checkValidity()',
    '@change' => 'formValid = $el.checkValidity()'
]) ?>

<?= $this->FormField->control('salutation', [
    'label' => __('Salutation'),
    'type' => 'select',
    'empty' => __('Please select...'),
    'class' => 'w-full',
    'required' => false
]) ?>

<?= $this->FormField->control('full_name', [
    'label' => __('Full Name'),
    'required' => true,
    'placeholder' => __('Text'),
    'class' => 'w-full'
]) ?>

<?= $this->FormField->control('username', [
    'type' => 'email',
    'label' => __('Email Address'),
    'required' => true,
    'placeholder' => __('name@domain.com'),
    'icon' => 'mail',
    'class' => 'w-full',
    'error_messages' => [
        __('Please enter a valid email address')
    ]
]) ?>

<?= $this->FormField->control('company', [
    'label' => __('Company / Organization'),
    'placeholder' => __('Text'),
    'class' => 'w-full',
    'required' => false
]) ?>

<?= $this->FormField->control('password', [
    'label' => __('Password'),
    'required' => true,
    'placeholder' => __('Text'),
    'class' => 'w-full',
    'pattern' => '^(?=.*[0-9])(?=.*[\.,!@#$%^&*?]).{8,}$',
    'help' => __('min. 8 characters, including at least one number and one special character (?!&$ etc.)'),
    'error_messages' => [
        __('Password must be at least 8 characters and include at least one number and one special character')
    ]
]) ?>

<div class="flex items-start gap-3">
    <?= $this->element('atoms/form_checkbox', [
        'name' => 'accept_beta_disclaimer',
        'id' => 'accept-beta-disclaimer',
        'label' => '',
        'required' => true,
        'value' => '1',
        'standalone' => true
    ]) ?>
    <div class="flex-1">
        <label for="accept-beta-disclaimer" class="font-semibold text-brand-deep text-base cursor-pointer block">
            <?= __('Hinweis zur Betaversion des Prüfportals') ?>
        </label>

        <div class="text-sm text-gray-600 mt-1" x-data="{ open: false }">
            <p class="mb-2 text-sm">
                <?= __('Dieses Prüfportal befindet sich derzeit in einer Betaversion und wird aktiv weiterentwickelt.') ?>
            </p>

            <button
                type="button"
                class="text-xs fo text-gray-500 hover:text-gray-700 mb-2"
                @click="open = !open"
            >
                <span x-show="!open"><?= __('Mehr anzeigen') ?></span>
                <span x-show="open"><?= __('Weniger anzeigen') ?></span>
            </button>

            <div x-show="open" x-collapse>
                <p class="mb-2 text-sm">
                    <?= __('Die bereitgestellten Funktionen, Inhalte und Prozesse können daher unvollständig sein, Fehler aufweisen oder sich jederzeit ändern. Es kann außerdem zu Abweichungen vom MISSION KI Qualitätsstandard kommen, da einzelne Module, Darstellungen oder Bewertungslogiken noch nicht final implementiert sind. Ebenso befinden sich Teile des Designs und der Benutzerführung noch in Überarbeitung, sodass Darstellungen, Bezeichnungen oder Interaktionen nicht dem späteren Endzustand entsprechen müssen. Während der Beta-Phase können wir keine durchgehende Verfügbarkeit, fehlerfreie Funktionsweise oder vollständige Korrektheit der angezeigten Informationen gewährleisten.') ?>
                </p>
                <p class="mb-2 text-sm">
                    <?= __('Hochgeladene Dokumente werden verschlüsselt übertragen und gespeichert. Dennoch empfehlen wir ausdrücklich, in der aktuellen Testphase keine vertraulichen, sensiblen oder personenbezogenen Unterlagen über das Portal zu übermitteln, da sicherheitsrelevante Funktionen, organisatorische Schutzmaßnahmen und interne Prüfprozesse noch erweitert und finalisiert werden.') ?>
                </p>
                <p class="mb-2 text-sm">
                    <?= __('Die angebotene Selbstprüfung dient ausschließlich der freiwilligen internen Bewertung durch die teilnehmende Organisation. Sie stellt keine behördliche Prüfung, Zertifizierung oder rechtsverbindliche Bewertung dar. acatech übernimmt keine Gewähr für Vollständigkeit, Richtigkeit oder rechtliche Wirkung der Ergebnisse dieser Selbstprüfung. Die Nutzung erfolgt auf eigenes Risiko und in eigener Verantwortung des teilnehmenden Unternehmens. acatech haftet nur bei Vorsatz oder grober Fahrlässigkeit sowie in Fällen gesetzlicher Haftung.') ?>
                </p>
            </div>

            <p class="mt-2 text-sm">
                <?= __('Mit der Nutzung des Prüfportals in der Betaphase erkennen Sie diese Einschränkungen und Hinweise ausdrücklich an.') ?>
            </p>
        </div>
    </div>
</div>

<div class="mt-6">
    <?= $this->element('atoms/button', [
        'label' => __('Register now'),
        'variant' => 'primary',
        'size' => 'MD',
        'type' => 'submit',
        'options' => [
            'class' => 'w-full',
            'x-bind:disabled' => '!formValid'
        ]
    ]) ?>
</div>

<?= $this->Form->end() ?>

<div class="mt-4">
    <p class="text-gray-600 text-sm">
        <?= __('Already registered?') ?>
        <?= $this->Html->link(
            __('Go to login'),
            ['controller' => 'Users', 'action' => 'login'],
            ['class' => 'text-brand-deep font-semibold underline']
        ) ?>
    </p>
</div>

<?php
$formContent = ob_get_clean();

// Render using shared auth layout
echo $this->element('organisms/app_auth_layout', [
    'title' => __('Register'),
    'subtitle' => __('Register to create assessment processes or participate in ongoing assessments.'),
    'content' => $formContent,
    'show_footer' => true,
    'show_docs_button' => false,
    'allow_scroll' => true  // Tall form needs scrolling
]);
?>
