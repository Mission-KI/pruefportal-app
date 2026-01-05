<?php
/**
 * @var \App\View\AppView $this
 * @var array $indicatorsList
 * @var \App\Model\Entity\Indicator $indicators
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\AppController $observables
 * @var \App\Controller\IndicatorsController $qualityDimension
 * @var \App\Controller\IndicatorsController $shortTitles
 * @var \App\Controller\IndicatorsController $vcioConfig
 * @var array $commentReferences Array of field names that have comments
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Validierung');
$this->assign('title', $title_for_layout);

$levelLabels = [
    3 => __(' (vollständig erfüllt)'),
    2 => __(' (wesentlich erfüllt)'),
    1 => __(' (im Ansatz erfüllt)'),
    0 => __(' (nicht erfüllt)'),
];

$this->assign('reserve_sidebar_space', 'true');
$this->start('right_sidebar');

echo $this->element('molecules/card', [
    'title' => __('Aktionen'),
    'heading_level' => 'h3',
    'heading_size' => 'text-md',
    'heading_weight' => 'font-semibold',
    'body' =>
        '<div class="space-y-3">' .
        $this->element('atoms/button', [
            'label' => __('Entwurf speichern'),
            'icon' => 'save-01',
            'iconPosition' => 'trailing',
            'variant' => 'primary',
            'size' => 'SM',
            'type' => 'button',
            'options' => ['class' => 'w-full', 'id' => 'save-draft-btn']
        ]) .
        '</div>',
    'variant' => 'plain',
    'escape' => false,
    'options' => ['class' => 'mb-6 px-3 py-3 bg-white rounded-lg shadow-sm border [&_h3]:!text-brand [&_h3]:mb-3']
]);

$steps = [];
foreach ($shortTitles as $qualityDimensionKey => $shortTitle) {
    $status = 'upcoming';
    $url = ['action' => 'validate', $process->id, $qualityDimensionKey];

    if ($qualityDimensionKey === $qualityDimension) {
        $status = 'current';
    }
    if (array_key_exists($vcioConfig[$qualityDimensionKey]['quality_dimension_id'], $indicatorsList)) {
        $url = false;
        $status = 'completed';
    }

    $steps[] = [
        'title' => $shortTitle,
        'status' => $status,
        'url' => $url,
        'key' => $qualityDimensionKey
    ];
}

echo $this->element('molecules/step_navigation', [
    'title' => __('VCIO-Validierung'),
    'steps' => $steps
]);

$this->end();

// Enable right sidebar by setting a non-empty value
$this->assign('show_right_sidebar', 'true');
?>

<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<div class="container mx-auto py-8" x-data="validationAutoSave()">
    <?= $this->element('process_status', ['process' => $process]); ?>

    <?= $this->element('molecules/primary_card', [
        'title' => __('Einstufung des KI-Systems nach der VCIO-Systematik'),
        'subtitle' => __('VCIO-Einstufung'),
        'body' => __('Für jede der Qualitätsdimensionen des Qualitätsstandards, bezüglich deren Kriterien ein Schutzbedarf in der vorangegangenen Analyse durch den/die PrüferIn festgestellt wurde, nehmen Sie nun eine Selbsteinstufung des KI-Systems vor und liefern bitte die geforderten Evidenzen, um diese Einstufung zu belegen.'),
        'escape' => false
    ]) ?>


    <h2 class="text-xl my-4 text-primary">
        <?= $this->element('atoms/icon', ['name' => $vcioConfig[$qualityDimension]['icon'], 'size' => 'xl', 'options' => ['class' => 'text-primary']]) ?>
        <?= $vcioConfig[$qualityDimension]['title'] ?> (<?= $qualityDimension ?>)
    </h2>

    <?= $this->Form->create(null, [
        'url' => ['action' => 'validate', $process->id, $qualityDimension],
        'class' => 'needs-validation',
        'id' => 'validation-form',
        'novalidate' => true,
    ]) ?>
<?php
    $modals = [];
    foreach ($indicators as $indicator):
        foreach ($vcioConfig[$qualityDimension]['criteria'] as $formIndex => $criterionContent):
            if (!array_key_exists($indicator->title, $criterionContent['indicators'])) {
                continue;
            }
            $indicatorContent = $criterionContent['indicators'][$indicator->title];

            $hasComments = in_array($indicator->title, $commentReferences);
            $commentIcon = $hasComments ? 'annotation' : 'message-plus-square';
            $commentUrl = $hasComments
                ? ['controller' => 'Comments', 'action' => 'ajax_view', $process->id, $indicator->title]
                : ['controller' => 'Comments', 'action' => 'ajax_add', $process->id];
            $modalId = 'modal-' . $formIndex . uniqid();
            $modals[$modalId] = $indicator->title;
?>
        <div class="mki-form-field-wrapper mb-4">
            <div class="inline-flex items-baseline gap-2">
                <span class="mki-form-field-index-badge" data-reference="<?= h($indicator->title) ?>" data-indicator-id="<?= $indicator->id ?>"><?= h($indicator->title) ?></span>
            </div>
            <div class="mki-form-field-label-wrapper flex items-center justify-between mb-2">
                <div class="text-brand-deep font-normal text-xl">
                    <?= h($indicatorContent['title']) ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (!empty($indicatorContent['tooltip'])): ?>
                        <div x-data="{ open: false }" class="relative inline-block">
                            <button type="button"
                                @click="open = !open"
                                @click.away="open = false"
                                class="bg-transparent border-none p-0 cursor-pointer flex items-center"
                                :class="{ 'text-brand-light-web': open, 'text-gray-500': !open }">
                                <?= $this->element('atoms/icon', [
                                    'name' => 'help-circle',
                                    'size' => 'sm',
                                    'options' => ['class' => 'w-5 h-5']
                                ]) ?>
                            </button>
                            <div x-show="open"
                                x-transition
                                class="fixed left-[var(--tooltip-left)] -translate-x-1/2 bottom-[var(--tooltip-bottom)] z-[9999] bg-brand-deep text-white p-4 rounded-[var(--radius-md)] shadow-[var(--shadow-lg)] min-w-64 max-w-80"
                                x-init="$watch('open', value => {
                                    if (value) {
                                        const rect = $el.previousElementSibling.getBoundingClientRect();
                                        $el.style.setProperty('--tooltip-left', rect.left + rect.width / 2 + 'px');
                                        $el.style.setProperty('--tooltip-bottom', window.innerHeight - rect.top + 8 + 'px');
                                    }
                                })">
                                <div class="absolute left-1/2 -translate-x-1/2 bottom-[-0.5rem] w-0 h-0 border-l-[0.5rem] border-l-transparent border-r-[0.5rem] border-r-transparent border-t-[0.5rem] border-t-brand-deep"></div>
                                <div class="text-sm leading-normal">
                                    <?= $indicatorContent['tooltip'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?= $this->element('molecules/docs_link', ['docs_id' => $indicatorContent['docs_id'] ?? '']) ?>

        <?= $this->element('organisms/vcio_definitions', ['indicatorContent' => $indicatorContent, 'observables' => $observables]) ?>

        <?= $this->element('molecules/vcio_level_display', [
            'title' => __('Selbsteinschätzung'),
            'selectedLevel' => $indicator->level_candidate,
            'levelLabels' => $levelLabels,
            'observables' => $observables
        ]) ?>

        <div class="mki-form-field-wrapper mb-4">
            <h3 class="text-lg font-semibold"><?= __('Evidenzen') ?></h3>
            <p>
            <?= nl2br($indicator->evidence) ?>
            </p>
            <?php if (!empty($indicator->uploads)): ?>
                <?= $this->element('molecules/attachments', [
                    'uploads' => $indicator->uploads
                ]) ?>
            <?php endif; ?>
        </div>
        <div class="mki-form-field-wrapper mb-4">
            <!-- Commentary Button -->
            <button
                type="button"
                class="self-start bg-transparent border-none p-1 cursor-pointer text-brand-light-web hover:text-brand-deep transition-colors"
                data-modal-trigger="<?= h($modalId) ?>"
                data-modal-url="<?= $this->Url->build($commentUrl) ?>"
                data-field-index="<?= h($criterionContent['index']) ?>"
                data-reference-id="<?= h($indicator->title) ?>"
                title="<?= $hasComments ? __('View comments') : __('Add comment') ?>">
                <?= $this->element('atoms/icon', [
                    'name' => $commentIcon,
                    'size' => 'sm',
                    'options' => ['class' => 'w-5 h-5']
                ]) ?>
            </button>
            <h3 class="text-lg font-semibold"><?= __('Einschätzung Prüfer') ?></h3>
            <?= $this->Form->control('indicators['.$indicator->id.'][id]', ['value' => $indicator->id, 'type' => 'hidden']); ?>
            <?= $this->element('organisms/vcio_selection', ['formIndex' => $indicator->id, 'indicatorKey' => $indicator->title, 'levelLabels' => $levelLabels, 'observables' => $observables, 'levelName' => 'level_examiner', 'existingIndicator' => $indicator]) ?>
        </div>
<?php
        endforeach;
    endforeach;
?>

    <?= $this->element('molecules/mobile_form_actions', [
        'justify' => 'end',
        'body' => $this->element('atoms/button', [
            'label' => __('Next step'),
            'variant' => 'primary',
            'size' => 'MD',
            'type' => 'submit',
            'options' => ['data-testid' => 'validation-next-step']
        ])
    ]) ?>

    <?= $this->Form->end() ?>

    <?= $this->element('atoms/autosave_indicator', ['prefix' => 'save-icon-template', 'className' => 'save-indicator']) ?>

</div>

<?php
    foreach($modals as $modalId => $title):
        echo $this->element('molecules/modal', [
            'id' => $modalId,
            'title' => __('Comment for {0}', h($title)),
            'content' => 'Loading',
            'size' => 'md'
        ]);
    endforeach;
?>

<script>
function validationAutoSave() {
    return {
        processId: <?= $process->id ?>,
        qualityDimensionId: '<?= $qualityDimension ?>',
        userId: <?= $this->request->getAttribute('identity')->id ?>,
        pendingSaves: {},
        debounceTimer: null,
        isOnline: true,
        saveInProgress: false,

        init() {
            console.log('Validation auto-save initialized for process', this.processId, 'QD', this.qualityDimensionId);
            this.attachFieldListeners();

            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());
            window.addEventListener('save-draft', () => this.saveAllIndicators());
        },

        saveAllIndicators() {
            if (this.saveInProgress) {
                console.log('Save already in progress');
                return;
            }

            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = null;
            }

            const form = document.querySelector('#validation-form');
            if (!form) return;

            const indicatorsData = this.collectAllIndicatorData(form);
            const indicatorIds = Object.keys(indicatorsData);

            if (indicatorIds.length === 0) {
                console.log('No indicators with level selected');
                this.showGlobalFeedback('info', '<?= __('Keine Daten zum Speichern vorhanden.') ?>');
                return;
            }

            console.log('Explicit save for indicators:', indicatorIds);

            indicatorIds.forEach(id => this.showFieldFeedback(id, 'saving'));

            this.saveInProgress = true;
            this.autoSaveIndicators(indicatorsData)
                .then(() => {
                    indicatorIds.forEach(id => this.showFieldFeedback(id, 'success'));
                    this.showGlobalFeedback('success', '<?= __('Entwurf erfolgreich gespeichert') ?>');
                })
                .catch(error => {
                    console.error('Explicit save failed:', error);
                    indicatorIds.forEach(id => this.showFieldFeedback(id, 'error'));
                    this.showGlobalFeedback('error', '<?= __('Speichern fehlgeschlagen') ?>');
                })
                .finally(() => {
                    this.saveInProgress = false;
                });
        },

        collectAllIndicatorData(form) {
            const indicatorsData = {};
            const radios = form.querySelectorAll('input[type="radio"][name^="indicators["]:checked');

            radios.forEach(radio => {
                const match = radio.name.match(/indicators\[(\d+)\]\[level_examiner\]/);
                if (match) {
                    const indicatorId = match[1];
                    indicatorsData[indicatorId] = {
                        level_examiner: parseInt(radio.value)
                    };
                }
            });

            return indicatorsData;
        },

        attachFieldListeners() {
            const form = document.querySelector('#validation-form');
            if (!form) return;

            const radios = form.querySelectorAll('input[type="radio"][name^="indicators["]');
            radios.forEach(radio => {
                radio.addEventListener('change', (e) => this.handleFieldChange(e));
            });

            const saveDraftBtn = document.getElementById('save-draft-btn');
            if (saveDraftBtn) {
                saveDraftBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('save-draft'));
                });
            }

            console.log('Attached field listeners to', radios.length, 'radio buttons');
        },

        handleFieldChange(event) {
            const input = event.target;
            const match = input.name.match(/indicators\[(\d+)\]/);
            if (!match) return;

            const indicatorId = match[1];
            this.pendingSaves[indicatorId] = true;

            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            this.debounceTimer = setTimeout(() => {
                this.executeBatchSave();
            }, 2000);

            console.log('Queued indicator', indicatorId, 'for save');
        },

        async executeBatchSave() {
            const indicesToSave = Object.keys(this.pendingSaves);
            if (indicesToSave.length === 0) return;

            console.log('Executing batch save for indicators:', indicesToSave);

            const form = document.querySelector('#validation-form');
            if (!form) return;

            const indicatorsData = {};
            indicesToSave.forEach(indicatorId => {
                const radio = form.querySelector(`input[name="indicators[${indicatorId}][level_examiner]"]:checked`);
                if (radio) {
                    indicatorsData[indicatorId] = {
                        level_examiner: parseInt(radio.value)
                    };
                    this.showFieldFeedback(indicatorId, 'saving');
                }
            });

            if (Object.keys(indicatorsData).length === 0) {
                console.log('No valid data to save');
                this.pendingSaves = {};
                return;
            }

            if (!this.isOnline) {
                console.log('Offline - cannot save');
                indicesToSave.forEach(id => this.showFieldFeedback(id, 'error'));
                this.showGlobalFeedback('error', '<?= __('Keine Internetverbindung') ?>');
                return;
            }

            this.saveInProgress = true;
            try {
                await this.autoSaveIndicators(indicatorsData);
                indicesToSave.forEach(id => this.showFieldFeedback(id, 'success'));
                this.pendingSaves = {};
            } catch (error) {
                console.error('Auto-save failed:', error);
                indicesToSave.forEach(id => this.showFieldFeedback(id, 'error'));
            } finally {
                this.saveInProgress = false;
            }
        },

        async autoSaveIndicators(indicatorsData) {
            const url = `/indicators/save-draft/${this.processId}`;
            const csrfToken = document.querySelector('input[name="_csrfToken"]')?.value;

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    indicators: indicatorsData
                })
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `HTTP ${response.status}`);
            }

            const result = await response.json();
            console.log('Save successful:', result);
            return result;
        },

        showFieldFeedback(indicatorId, state) {
            const badge = document.querySelector(`[data-indicator-id="${indicatorId}"]`);
            if (!badge) return;

            const container = badge.parentNode;
            if (!container) return;

            const existingIndicator = container.querySelector('.save-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }

            let templateId = null;
            if (state === 'saving') {
                templateId = 'save-icon-template-saving';
            } else if (state === 'success') {
                templateId = 'save-icon-template-success';
            } else if (state === 'error') {
                templateId = 'save-icon-template-error';
            }

            if (templateId) {
                const template = document.getElementById(templateId);
                if (!template) return;

                const indicator = template.cloneNode(true);
                indicator.removeAttribute('id');

                container.appendChild(indicator);

                if (state === 'success') {
                    setTimeout(() => {
                        indicator.remove();
                    }, 3000);
                }
            }
        },

        showGlobalFeedback(status, message) {
            if (typeof window.showFlash === 'function') {
                window.showFlash(message, status);
            }
        },

        handleOnline() {
            console.log('Coming online');
            this.isOnline = true;
        },

        handleOffline() {
            console.log('Going offline');
            this.isOnline = false;
        }
    };
}
</script>


