<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', __('Accept Invitation'));

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

<?= $this->FormField->control('set_new_password', [
    'type' => 'password',
    'label' => __('New Password'),
    'required' => true,
    'placeholder' => __('Enter your new password'),
    'class' => 'w-full',
    'pattern' => '^(?=.*[0-9])(?=.*[\.,!@#$%^&*?]).{8,}$',
    'minlength' => 8,
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
        'label' => __('Set Password'),
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

<div class="mt-4 text-center">
    <p class="text-gray-600 text-sm">
        <?= $this->Html->link(
            __('Back to Login'),
            ['controller' => 'Users', 'action' => 'login'],
            ['class' => 'text-brand-deep font-semibold underline']
        ) ?>
    </p>
</div>

<?php
$formContent = ob_get_clean();

// Render using shared auth layout
echo $this->element('organisms/app_auth_layout', [
    'title' => __('Accept Invitation'),
    'subtitle' => __('Please set a password for your account:') . ' <strong>' . h($user->username) . '</strong>',
    'content' => $formContent,
    'show_footer' => false,
    'show_docs_button' => false,
    'allow_scroll' => false
]);
?>
