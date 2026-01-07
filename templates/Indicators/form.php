<?php

/**
 * @var \App\View\AppView $this
 * @var array $indicators
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\AppController $observables
 * @var \App\Controller\IndicatorsController $qualityDimension
 * @var \App\Controller\IndicatorsController $relevances
 * @var \App\Controller\IndicatorsController $shortTitles
 * @var \App\Controller\IndicatorsController $vcioConfig
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Einstufung');
$this->assign('title', $title_for_layout);

$this->start('right_sidebar');

$items = [];
foreach ($shortTitles as $qualityDimensionKey => $shortTitle) {
    $status = 'upcoming';
    $url = ['action' => 'add', $process->id, $qualityDimensionKey];

    if ($qualityDimensionKey === $qualityDimension) {
        $status = 'current';
    }
    if (array_key_exists($vcioConfig[$qualityDimensionKey]['quality_dimension_id'], $indicators)) {
        $status = 'completed';
        $url = ['action' => 'edit', $process->id, $qualityDimensionKey];
    }

    $items[] = [
        'title' => $shortTitle,
        'key' => $qualityDimensionKey,
        'status' => $status,
        'url' => $url,
    ];
}

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

echo $this->element('molecules/workflow_navigation', [
    'title' => __('VCIO-Einstufung'),
    'overview_url' => ['controller' => 'Indicators', 'action' => 'index', $process->id],
    'items' => $items,
]);

$this->end();

// Enable right sidebar by setting a non-empty value
$this->assign('show_right_sidebar', 'true');

// Reserve space for the right sidebar to prevent content from spreading underneath it
$this->assign('reserve_sidebar_space', 'true');
?>
<!-- indicators form -->
<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<div class="container mx-auto py-8" x-data="indicatorAutoSave()">
    <?= $this->element('process_status', ['process' => $process]); ?>

    <?= $this->element('molecules/primary_card', [
        'title' => __('Einstufung des KI-Systems nach der VCIO-Systematik'),
        'subtitle' => __('VCIO-Einstufung'),
        'body' => __('Für jede der Qualitätsdimensionen des Qualitätsstandards, bezüglich deren Kriterien ein Schutzbedarf in der vorangegangenen Analyse durch den/die PrüferIn festgestellt wurde, nehmen Sie nun eine Selbsteinstufung des KI-Systems vor und liefern bitte die geforderten Evidenzen, um diese Einstufung zu belegen.'),
        'escape' => false,
    ]) ?>


    <h2 class="text-xl my-4 text-primary">
        <?= $this->element('atoms/icon', ['name' => $vcioConfig[$qualityDimension]['icon'], 'size' => 'xl', 'options' => ['class' => 'text-primary']]) ?>
        <?= $vcioConfig[$qualityDimension]['title'] ?> (<?= $qualityDimension ?>)
    </h2>

    <?= $this->Form->create(null, [
        'url' => ['action' => isset($existingIndicators) ? 'edit' : 'add', $process->id, $qualityDimension],
        'type' => 'file',
        'class' => 'needs-validation',
        'id' => 'indicators-form',
        'novalidate' => true,
    ]) ?>
    <?php
    $formIndex = 0;
    $levelLabels = [
        3 => __(' (vollständig erfüllt)'),
        2 => __(' (wesentlich erfüllt)'),
        1 => __(' (im Ansatz erfüllt)'),
        0 => __(' (nicht erfüllt)'),
    ];

    foreach ($vcioConfig[$qualityDimension]['criteria'] as $criterion):
        // Only relevant Indicators relating to the CriterionType according to the Criteria are displayed
        if(array_key_exists($criterion['criterion_type_id'], $relevances) && $relevances[$criterion['criterion_type_id']] !== false):
            foreach ($criterion['indicators'] as $indicatorKey => $indicatorContent):
                // Get existing indicator data if in edit mode
                $existingIndicator = isset($existingData) && isset($existingData[$indicatorKey]) ? $existingData[$indicatorKey] : null;
    ?>
            <div class="mki-form-field-wrapper mb-4">
                <div class="inline-flex items-baseline gap-2">
                    <span class="mki-form-field-index-badge" data-reference="<?= h($indicatorKey) ?>" data-indicator-index="<?= $formIndex ?>"><?= h($indicatorKey) ?></span>
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

                <?= $this->element('molecules/docs_link', ['docs_id' => $indicatorContent['docs_id'] ?? '']) ?>

                <?= $this->element('organisms/vcio_definitions', ['indicatorContent' => $indicatorContent, 'observables' => $observables]) ?>

                <div class="mb-4 p-0">
                    <h3 class="text-lg font-semibold mb-2 text-primary"><?= __('Selbsteinschätzung') ?></h3>
                    <?php if ($existingIndicator): ?>
                        <?= $this->Form->control('indicators[' . $formIndex . '][id]', ['type' => 'hidden', 'value' => $existingIndicator->id]) ?>
                    <?php endif; ?>
                    <?= $this->Form->control('indicators[' . $formIndex . '][title]', ['type' => 'hidden', 'value' => $indicatorKey]) ?>

                    <?= $this->Form->control('indicators[' . $formIndex . '][process_id]', ['type' => 'hidden', 'value' => $process->id]) ?>
                    <?= $this->Form->control('indicators[' . $formIndex . '][quality_dimension_id]', ['type' => 'hidden', 'value' => $vcioConfig[$qualityDimension]['quality_dimension_id']]) ?>
                    <?= $this->element('organisms/vcio_selection', ['formIndex' => $formIndex, 'indicatorKey' => $indicatorKey, 'levelLabels' => $levelLabels, 'observables' => $observables, 'levelName' => 'level_candidate', 'existingIndicator' => $existingIndicator]) ?>
                </div>
                <div class="mb-4 p-0">
                    <?= $this->element('molecules/form_field', [
                        'name' => 'indicators[' . $formIndex . '][evidence]',
                        'label' => __('Evidence'),
                        'type' => 'textarea',
                        'required' => true,
                        'error_messages' => [__('Please enter Evidences.')],
                        'atom_element' => 'atoms/form_textarea',
                        'atom_data' => [
                            'name' => 'indicators[' . $formIndex . '][evidence]',
                            'placeholder' => __('Text'),
                            'required' => true,
                            'value' => $existingIndicator ? $existingIndicator->evidence : ''
                        ]
                    ]) ?>

                    <div class="mt-4 p-0">
                        <?= $this->element('molecules/file_upload_with_preview', [
                            'name' => 'indicators[' . $formIndex . '][attachments]',
                            'id' => 'indicator-files-' . $formIndex,
                            'button_label' => __('Dateien anhängen'),
                            'button_icon' => 'plus-square',
                            'button_variant' => 'secondary',
                            'button_size' => 'SM',
                            'accept' => '.pdf,.doc,.docx,.png,.jpg,.jpeg',
                            'multiple' => true
                        ]) ?>
                    </div>
                </div>
            </div>
    <?php
            $formIndex++;
            endforeach;
        endif;
    endforeach;
    ?>


    <?= $this->element('molecules/mobile_form_actions', [
        'justify' => 'end',
        'body' => $this->element('atoms/button', [
            'label' => __('Next step'),
            'variant' => 'primary',
            'size' => 'MD',
            'type' => 'submit'
        ])
    ]) ?>

    <?= $this->Form->end() ?>

    <?= $this->element('atoms/autosave_indicator', ['prefix' => 'save-icon-template', 'className' => 'save-indicator']) ?>

</div>

<script>
// Client-side validation on submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('indicators-form');
    if (!form) return;

    // Clear error state when radio button is selected
    form.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const container = this.closest('.mki-form-field-container');
            if (container) {
                // Clear error state by dispatching input event
                this.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });

    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            // Trigger Alpine.js validation by dispatching blur events
            const invalidFields = form.querySelectorAll(':invalid');
            invalidFields.forEach(field => {
                field.dispatchEvent(new FocusEvent('blur', { bubbles: true }));
            });

            // Scroll to first error
            const firstInvalid = invalidFields[0];
            if (firstInvalid) {
                const container = firstInvalid.closest('.mki-form-field-container');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return false;
        }
    }, true);

    // Handle Save Draft button click
    const saveDraftBtn = document.getElementById('save-draft-btn');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('save-draft'));
        });
    }
});

// Alpine.js Auto-Save Component
function indicatorAutoSave() {
    return {
        // Data structure
        processId: <?= $process->id ?>,
        qualityDimensionId: '<?= $qualityDimension ?>',
        userId: <?= $this->request->getAttribute('identity')->id ?>,
        originalValues: {},
        pendingSaves: {},
        savedFields: {},
        debounceTimer: null,
        isOnline: true,
        saveInProgress: false,

        // Initialize
        init() {
            console.log('Auto-save initialized for process', this.processId, 'QD', this.qualityDimensionId);
            this.captureOriginalValues();
            this.attachFieldListeners();

            // Online/offline detection
            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());

            // Listen for explicit save-draft event
            window.addEventListener('save-draft', () => this.saveAllIndicators());
        },

        saveAllIndicators() {
            if (this.saveInProgress) {
                console.log('Save already in progress');
                return;
            }

            // Cancel any pending debounced save
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = null;
            }

            const form = document.querySelector('#indicators-form');
            if (!form) return;

            // Find all indicator indices that have data
            const indicatorIndices = new Set();
            const inputs = form.querySelectorAll('input[name^="indicators["], textarea[name^="indicators["], select[name^="indicators["]');
            inputs.forEach(input => {
                const idx = this.getIndicatorIndex(input.name);
                if (idx !== null) {
                    indicatorIndices.add(idx);
                }
            });

            if (indicatorIndices.size === 0) {
                console.log('No indicators to save');
                this.showGlobalFeedback('info', '<?= __('Keine Änderungen zu speichern') ?>');
                return;
            }

            // Collect data for indicators that have meaningful data (level selected)
            const indicatorsData = {};
            indicatorIndices.forEach(index => {
                const data = this.collectIndicatorData(parseInt(index));
                // Only include indicators with a level selected (required by server validation)
                if (data && data.title && data.level_candidate !== null) {
                    indicatorsData[data.title] = {
                        level_candidate: data.level_candidate,
                        evidence: data.evidence,
                        quality_dimension_id: data.quality_dimension_id
                    };
                    this.pendingSaves[index] = true;
                }
            });

            if (Object.keys(indicatorsData).length === 0) {
                console.log('No indicators with level selected');
                this.showGlobalFeedback('info', '<?= __('Keine Daten zum Speichern vorhanden. Bitte wählen Sie mindestens eine Einstufung aus.') ?>');
                return;
            }

            console.log('Explicit save for indicators:', Object.keys(indicatorsData));

            // Show saving state on all indicators
            indicatorIndices.forEach(index => {
                this.showFieldFeedback(index, 'saving');
            });

            // Perform save
            this.saveInProgress = true;
            this.autoSaveIndicators(indicatorsData)
                .then(() => {
                    this.showGlobalFeedback('success', '<?= __('Entwurf erfolgreich gespeichert') ?>');
                })
                .catch(error => {
                    console.error('Explicit save failed:', error);
                    this.showGlobalFeedback('error', '<?= __('Speichern fehlgeschlagen') ?>');
                })
                .finally(() => {
                    this.saveInProgress = false;
                });
        },

        showGlobalFeedback(status, message) {
            if (typeof window.showFlash === 'function') {
                window.showFlash(message, status);
            }
        },

        // Capture initial field values
        captureOriginalValues() {
            const form = document.querySelector('#indicators-form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[type="radio"], input[type="checkbox"], select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('indicators[')) {
                    const fieldName = this.extractFieldName(input.name);
                    this.originalValues[fieldName + '_' + input.name.split('[')[2]] = this.getFieldValue(input);
                }
            });

            console.log('Captured original values:', this.originalValues);
        },

        // Extract indicator title from field name: indicators[0][title] → indicators[0]
        extractFieldName(name) {
            const match = name.match(/indicators\[(\d+)\]/);
            return match ? match[0] : name;
        },

        // Get indicator index from field name: indicators[0][title] → 0
        getIndicatorIndex(name) {
            const match = name.match(/indicators\[(\d+)\]/);
            return match ? parseInt(match[1]) : null;
        },

        // Get field value based on input type
        getFieldValue(input) {
            if (input.type === 'checkbox') {
                return input.checked;
            } else if (input.type === 'radio') {
                const checked = document.querySelector(`input[name="${input.name}"]:checked`);
                return checked ? checked.value : '';
            } else {
                return input.value;
            }
        },

        // Attach blur listeners to form fields
        attachFieldListeners() {
            const form = document.querySelector('#indicators-form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[type="radio"], input[type="checkbox"], select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('indicators[')) {
                    // Blur event for all fields
                    input.addEventListener('blur', (e) => this.handleFieldBlur(e));

                    // Change event for radios (immediate feedback)
                    if (input.type === 'radio') {
                        input.addEventListener('change', (e) => this.handleFieldBlur(e));
                    }
                }
            });

            console.log('Attached field listeners to', inputs.length, 'inputs');
        },

        // Handle field blur event
        handleFieldBlur(event) {
            const input = event.target;
            const fieldName = this.extractFieldName(input.name);
            const indicatorIndex = this.getIndicatorIndex(input.name);

            if (indicatorIndex === null) return;

            const currentValue = this.getFieldValue(input);
            const originalKey = fieldName + '_' + input.name.split('[')[2];
            const originalValue = this.originalValues[originalKey];

            // Only queue save if value has changed
            if (currentValue !== originalValue) {
                console.log('Field changed:', input.name, 'from', originalValue, 'to', currentValue);
                this.queueIndicatorSave(indicatorIndex);
            }
        },

        // Queue indicator for save with debounce
        queueIndicatorSave(indicatorIndex) {
            // Mark as pending
            this.pendingSaves[indicatorIndex] = true;

            // Clear existing timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            // Set new timer (2 seconds)
            this.debounceTimer = setTimeout(() => {
                this.executeBatchSave();
            }, 2000);

            console.log('Queued indicator', indicatorIndex, 'for save');
        },

        // Collect indicator data from form
        collectIndicatorData(indicatorIndex) {
            const form = document.querySelector('#indicators-form');
            if (!form) return null;

            const titleInput = form.querySelector(`input[name="indicators[${indicatorIndex}][title]"]`);
            if (!titleInput) return null;

            const title = titleInput.value;
            const levelRadio = form.querySelector(`input[name="indicators[${indicatorIndex}][level_candidate]"]:checked`);
            const evidenceTextarea = form.querySelector(`textarea[name="indicators[${indicatorIndex}][evidence]"]`);
            const qualityDimensionInput = form.querySelector(`input[name="indicators[${indicatorIndex}][quality_dimension_id]"]`);

            return {
                title: title,
                level_candidate: levelRadio ? parseInt(levelRadio.value) : null,
                evidence: evidenceTextarea ? evidenceTextarea.value : '',
                quality_dimension_id: qualityDimensionInput ? parseInt(qualityDimensionInput.value) : null
            };
        },

        // Execute batch save
        async executeBatchSave() {
            const indicesToSave = Object.keys(this.pendingSaves);
            if (indicesToSave.length === 0) return;

            console.log('Executing batch save for indicators:', indicesToSave);

            // Collect data for all pending indicators
            const indicatorsData = {};
            indicesToSave.forEach(index => {
                const data = this.collectIndicatorData(parseInt(index));
                if (data && data.title) {
                    indicatorsData[data.title] = {
                        level_candidate: data.level_candidate,
                        evidence: data.evidence,
                        quality_dimension_id: data.quality_dimension_id
                    };
                }
            });

            if (Object.keys(indicatorsData).length === 0) {
                console.log('No valid data to save');
                this.pendingSaves = {};
                return;
            }

            // Check if online
            if (!this.isOnline) {
                console.log('Offline - saving to localStorage');
                this.saveToLocalStorage(indicatorsData);
                this.handleSaveError('Offline - data saved locally');
                return;
            }

            // Show saving indicators
            indicesToSave.forEach(index => {
                this.showFieldFeedback(index, 'saving');
            });

            // Perform save
            this.saveInProgress = true;
            try {
                await this.autoSaveIndicators(indicatorsData);
            } catch (error) {
                console.error('Auto-save failed:', error);
                this.handleSaveError(error.message || 'Save failed');
                this.saveToLocalStorage(indicatorsData);
            } finally {
                this.saveInProgress = false;
            }
        },

        // Auto-save indicators via fetch
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
            this.handleSaveSuccess(result);
            return result;
        },

        // Handle successful save
        handleSaveSuccess(result) {
            console.log('Save successful:', result);

            // Mark saved indicators
            Object.keys(this.pendingSaves).forEach(index => {
                this.savedFields[index] = true;
                this.showFieldFeedback(index, 'success');
            });

            // Clear pending saves
            this.pendingSaves = {};

            // Update original values to match saved state
            this.captureOriginalValues();

            // Clear localStorage backup
            localStorage.removeItem(`indicators_autosave_${this.userId}_${this.processId}_${this.qualityDimensionId}`);

            console.log('Saved at', result.timestamp);
        },

        // Handle save error
        handleSaveError(errorMessage) {
            console.error('Save error:', errorMessage);

            // Mark fields with error state
            Object.keys(this.pendingSaves).forEach(index => {
                this.savedFields[index] = false;
                this.showFieldFeedback(index, 'error');
            });

            // Don't clear pendingSaves - allow retry
        },

        // Save to localStorage as backup
        saveToLocalStorage(indicatorsData) {
            const storageKey = `indicators_autosave_${this.userId}_${this.processId}_${this.qualityDimensionId}`;
            const storageData = {
                timestamp: new Date().toISOString(),
                data: indicatorsData
            };

            try {
                localStorage.setItem(storageKey, JSON.stringify(storageData));
                console.log('Saved to localStorage:', storageKey);
            } catch (error) {
                console.error('localStorage save failed:', error);
            }
        },

        // Show visual feedback for field/indicator
        showFieldFeedback(indicatorIndex, state) {
            const badge = document.querySelector(`.mki-form-field-index-badge[data-indicator-index="${indicatorIndex}"]`);
            if (!badge) return;

            this.showBadgeIndicator(badge, state);
        },

        // Show badge indicator (checkmark, warning, spinner)
        showBadgeIndicator(badge, state) {
            // Find the flex container (parent of badge)
            const container = badge.parentNode;
            if (!container) return;

            // Remove existing indicator
            const existingIndicator = container.querySelector('.save-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }

            // Get the appropriate icon template
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

                // Clone the template
                const indicator = template.cloneNode(true);
                indicator.removeAttribute('id');

                // Append to container (flex container with gap handles spacing)
                container.appendChild(indicator);

                // Auto-remove success indicator after 3 seconds
                if (state === 'success') {
                    setTimeout(() => {
                        indicator.remove();
                    }, 3000);
                }
            }
        },

        // Handle online/offline events
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

