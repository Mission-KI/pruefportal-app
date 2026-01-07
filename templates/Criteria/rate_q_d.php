<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Criterion $criterion
 * @var \App\Model\Entity\Criterion $criteria
 * @var \App\Model\Entity\Process $process
 * @var \App\Controller\AppController $currentLanguage
 * @var \App\Controller\CriteriaController $protectionNeedsAnalysis
 * @var \App\Controller\AppController $questionTypes [__('Applikationsfragen'), __('Grundfragen'), __('Erweiterungsfragen')]
 * @var \App\Controller\AppController $qualityDimensions
 * @var string $quality_dimension
 * @var bool $is_edit
 * @var int $question_id
 */
$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('Protection Needs Analysis');
$this->assign('title', $title_for_layout);
$this->assign('pageClass', 'bg-gray-25');
$this->assign('show_right_sidebar', 'true');
$this->assign('reserve_sidebar_space', 'true');
?>
<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => $process->title,
    'size' => false,
    'weight' => false,
    'options' => ['class' => 'text-brand display-xs mb-6']
]) ?>

<div class="w-full mb-6">
    <?= $this->element('process_status', ['process' => $process]); ?>
</div>

<?= $this->element('molecules/primary_card', [
    'title' => $questionTypes[$question_id],
    'subtitle' => __('Schutzbedarfsanalyse'),
    'body' => $protectionNeedsAnalysis[$quality_dimension]['title'][$currentLanguage] . ' (' . $quality_dimension . ')',
    'icon' => $protectionNeedsAnalysis[$quality_dimension]['icon']
]) ?>

<?= $this->element('atoms/autosave_indicator', ['prefix' => 'pna-autosave', 'className' => 'save-indicator']) ?>

<div class="space-y-6" x-data="pnaAutoSave()">
    <?= $this->Form->create($criterion, [
        'id' => 'criterion-form',
        'class' => 'space-y-6',
        'novalidate' => true
    ]) ?>

    <?php
        echo $this->Form->control('quality_dimension_id', ['type' => 'hidden', 'value' => $protectionNeedsAnalysis[$quality_dimension]['quality_dimension_id']]);
        echo $this->Form->control('question_id', ['type' => 'hidden', 'value' => $question_id]);
        echo $this->Form->control('process_id', ['type' => 'hidden', 'value' => $process->id]);
    ?>
<?php
    // Get questions for the current step (question_id) - already filtered in controller
    $currentQuestions = $protectionNeedsAnalysis[$quality_dimension]['questions'][$question_id] ?? [];

    foreach ($currentQuestions as $key => $question) {
        // Rename JSON 'id' field to 'question_id' to avoid conflict with database record ID
        if (array_key_exists('id', $question) && $question['id'] > 0) {
            $currentQuestions[$key]['question_id'] = $question['id'];
            unset($currentQuestions[$key]['id']);
        }
    }

    if(isset($is_edit)) {
        echo $this->Form->control('is_edit', ['type' => 'hidden', 'value' => true]);
        // Extend the currentQuestions array with the database record id and value from db criterion
        foreach ($currentQuestions as $key => $question) {
            foreach ($criteria as $criterion) {
                if($criterion->title === $question['question_id']) {
                    $currentQuestions[$key]['id'] = $criterion->id;
                    $currentQuestions[$key]['value'] = $criterion->value;
                }
            }
        }
    }

    // Render using shared molecule
    echo $this->element('molecules/protection_needs_renderer', [
        'currentQuestions' => $currentQuestions,
    ]);
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
</div>

<?php $this->start('right_sidebar'); ?>

<?= $this->element('molecules/card', [
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
        $this->element('atoms/button', [
            'label' => __('Neuer Kommentar'),
            'icon' => 'message-plus-square',
            'iconPosition' => 'trailing',
            'url' => ['controller' => 'Processes', 'action' => 'comments', $process->id],
            'variant' => 'primary',
            'size' => 'SM',
            'options' => ['class' => 'w-full']
        ]) .
        '</div>',
    'variant' => 'plain',
    'escape' => false,
    'options' => ['class' => 'mb-6 px-3 py-3 bg-white rounded-lg shadow-sm border [&_h3]:!text-brand [&_h3]:mb-3']
]) ?>

<?= $this->element('molecules/card', [
    'title' => __('Bereitgestellte Dokumente'),
    'heading_level' => 'h3',
    'heading_size' => 'text-md',
    'heading_weight' => 'font-semibold',
    'body' => $this->element('molecules/document_list', [
        'uploads' => $process->uploads ?? [],
        'emptyMessage' => __('Noch keine Dokumente hochgeladen')
    ]),
    'variant' => 'plain',
    'escape' => false,
    'options' => ['class' => 'mb-6 px-3 py-3 bg-white rounded-lg shadow-sm border [&_h3]:!text-brand [&_h3]:mb-3']
]) ?>

<?php
$navItems = [];
foreach ($protectionNeedsAnalysis as $qdKey => $qd) {
    $qdState = $navigationState[$qdKey] ?? ['isComplete' => false, 'url' => null];

    $status = 'upcoming';
    if ($qdKey === $quality_dimension) {
        $status = 'current';
    } elseif ($qdState['isComplete']) {
        $status = 'completed';
    }

    $navItems[] = [
        'title' => $qd['short_title'],
        'key' => $qdKey,
        'status' => $status,
        'url' => $qdState['url'],
    ];
}
?>
<?= $this->element('molecules/workflow_navigation', [
    'title' => __('Schutzbedarfsanalyse'),
    'overview_url' => ['controller' => 'Criteria', 'action' => 'index', $process->id],
    'items' => $navItems,
]) ?>

<?php $this->end(); ?>

<script>
// Simple validation on submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('criterion-form');
    if (!form) return;

    // Handle Save Draft button click
    const saveDraftBtn = document.getElementById('save-draft-btn');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('save-draft'));
        });
    }

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
});
</script>

<script>
    /**
     * Updates related questions based on the selected value if the related question is already answered
     * @param {string} question_id - The ID of the question
     * @param {string} value - The selected value
     * @returns {Promise} - A promise that resolves to the response from the server
     */
    function updateRelatedQuestion(question_id, value) {
        const csrfToken = document.querySelector('input[name=\"_csrfToken\"]')?.value || '';
        const formData = new URLSearchParams({
            question_id: question_id,
            value: value,
            process_id: <?= $process->id ?>
        });

        return fetch('/criteria/check-for-related-question', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error updating related question:', error);
            return { success: false, error: error.message };
        });
    }

    const excludedClasses = ['js-related-question', 'mki-form-radio']; // Add classes to exclude
    const relatedQuestionClasses = [];
    const shownQuestionIds = []; // Track which questions have been shown
    const processedContainers = new Set(); // Track processed containers to avoid duplicates

    document.querySelectorAll('.js-related-question').forEach((input) => {
        const container = input.closest('.mki-form-field-container');
        if (!container || processedContainers.has(container)) return;
        processedContainers.add(container);

        // Get this question's ID from input name: criteria[TR-Z20][value] -> TR-Z20
        const nameMatch = input.name.match(/criteria\[([^\]]+)\]/);
        const questionId = nameMatch ? nameMatch[1] : null;

        // Get related question IDs from CSS classes
        const relatedIds = Array.from(input.classList).filter(cssClass => !excludedClasses.includes(cssClass));

        // Check if any related question was already shown (same-page duplicate)
        const shouldHide = relatedIds.some(relatedId => shownQuestionIds.includes(relatedId));

        if (shouldHide) {
            container.style.display = 'none';
        } else if (questionId) {
            shownQuestionIds.push(questionId);
        }

        // Sync values when a shown question changes - attach to all radios in this container
        container.querySelectorAll('.js-related-question').forEach((radio) => {
            radio.addEventListener('change', function(e) {
                const selectedValue = e.target.value;
                relatedIds.forEach((relatedQuestionId) => {
                    const otherInput = document.querySelector('input[name=\"criteria['+relatedQuestionId+'][value]\"][value=\"'+selectedValue+'\"]');
                    // related question is on the same page
                    if(otherInput !== null && !otherInput.disabled) {
                        otherInput.checked = true;
                    }
                });
            });
        });

        // Handle cross-dimension related questions (check DB)
        relatedIds.forEach((relatedQuestionId) => {
            if(!relatedQuestionClasses.includes(relatedQuestionId)) {
                relatedQuestionClasses.push(relatedQuestionId);
                if(document.getElementById(relatedQuestionId) === null) { // relatedQuestion is in a different quality_dimension
                    updateRelatedQuestion(relatedQuestionId, input.value)
                        .then(response => {
                            if(response.success && response.disable) {
                                // Hide the entire question container instead of disabling
                                if (container) {
                                    container.style.display = 'none';

                                    // Still set the value for form submission
                                    const parent = input.closest('.mki-form-radio-group');
                                    if (parent) {
                                        parent.querySelectorAll('input').forEach(function (elem) {
                                            if(response.value == elem.value) {
                                                elem.checked = true;
                                            }
                                        });
                                    }
                                }
                            }
                        })
                        .catch(error => console.error('Failed to update:', error));
                }
            }
        });
    });
</script>

<?php $this->start('script'); ?>
<script>
function pnaAutoSave() {
    return {
        // Data structure (same as UCD)
        processId: <?= $process->id ?>,
        qualityDimensionId: '<?= $quality_dimension ?>',
        originalValues: {},
        pendingSaves: {},
        savedFields: {},
        debounceTimer: null,
        isOnline: true,
        saveInProgress: false,

        // Initialize
        init() {
            this.captureOriginalValues();
            this.attachFieldListeners();

            // Online/offline detection
            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());

            // Listen for explicit save-draft event
            window.addEventListener('save-draft', () => this.saveAllCriteria());
        },

        saveAllCriteria() {
            if (this.saveInProgress) {
                console.log('Save already in progress');
                return;
            }

            // Cancel any pending debounced save
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = null;
            }

            const form = document.querySelector('#criterion-form');
            if (!form) return;

            // Collect all criteria with values (not just changed ones)
            const criteriaData = {};
            const processedTitles = new Set();

            const inputs = form.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked, select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('criteria[')) {
                    const title = this.extractFieldName(input.name);
                    if (!processedTitles.has(title)) {
                        processedTitles.add(title);
                        criteriaData[title] = this.collectCriterionData(title);

                        // Also add to pendingSaves for feedback
                        this.pendingSaves[title] = {
                            data: criteriaData[title],
                            element: input,
                            timestamp: Date.now()
                        };
                    }
                }
            });

            if (Object.keys(criteriaData).length === 0) {
                console.log('No criteria to save');
                this.showGlobalFeedback('info', '<?= __('Keine Änderungen zu speichern') ?>');
                return;
            }

            console.log('Explicit save for criteria:', Object.keys(criteriaData));
            this.autoSaveCriteria(criteriaData);
        },

        showGlobalFeedback(status, message) {
            if (typeof window.showFlash === 'function') {
                window.showFlash(message, status);
            }
        },

        // Capture initial field values
        captureOriginalValues() {
            const form = document.querySelector('#criterion-form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[type="radio"], input[type="checkbox"], select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('criteria[')) {
                    const fieldName = this.extractFieldName(input.name);
                    this.originalValues[fieldName] = this.getFieldValue(input);
                }
            });
        },

        // Extract criterion title from field name: criteria[TR-Z12][value] → TR-Z12
        extractFieldName(name) {
            const match = name.match(/criteria\[([^\]]+)\]/);
            return match ? match[1] : name;
        },

        // Get field value based on input type
        getFieldValue(input) {
            if (input.type === 'checkbox') {
                return input.checked;
            } else if (input.type === 'radio') {
                const escapedName = CSS.escape(input.name);
                const checked = document.querySelector(`input[name="${escapedName}"]:checked`);
                return checked ? checked.value : '';
            } else {
                return input.value;
            }
        },

        // Attach blur listeners to all question fields
        attachFieldListeners() {
            const form = document.querySelector('#criterion-form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[type="radio"], input[type="checkbox"], select, textarea');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('criteria[')) {
                    input.addEventListener('blur', (e) => this.handleFieldBlur(e));
                    // For radios, also listen to change event
                    if (input.type === 'radio') {
                        input.addEventListener('change', (e) => this.handleFieldBlur(e));
                    }
                }
            });
        },

        // Handle field blur event
        handleFieldBlur(event) {
            const input = event.target;
            const fieldName = this.extractFieldName(input.name);
            const currentValue = this.getFieldValue(input);
            const originalValue = this.originalValues[fieldName];

            // Only trigger save if value actually changed
            if (currentValue !== originalValue) {
                console.log('Field changed:', fieldName, 'from', originalValue, 'to', currentValue);
                this.queueCriterionSave(fieldName, input);
            }
        },

        // Queue criterion for batch save
        queueCriterionSave(fieldName, inputElement) {
            // Collect all data for this criterion
            const criterionData = this.collectCriterionData(fieldName);

            this.pendingSaves[fieldName] = {
                data: criterionData,
                element: inputElement,
                timestamp: Date.now()
            };

            // Show immediate visual feedback (saving state)
            this.showFieldFeedback(inputElement, 'saving');

            // Clear existing timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            // Set new timer (2 second debounce)
            this.debounceTimer = setTimeout(() => {
                this.executeBatchSave();
            }, 2000);
        },

        // Collect all data fields for a criterion (value, quality_dimension_id, etc.)
        collectCriterionData(title) {
            const form = document.querySelector('#criterion-form');
            const data = {};

            // Find all inputs for this criterion
            const escapedTitle = CSS.escape(title);
            const inputs = form.querySelectorAll(`[name^="criteria[${escapedTitle}]"]`);
            inputs.forEach(input => {
                const match = input.name.match(/criteria\[[^\]]+\]\[([^\]]+)\]/);
                if (match) {
                    const fieldKey = match[1]; // e.g., "value", "quality_dimension_id"
                    if (input.type === 'radio') {
                        if (input.checked) {
                            data[fieldKey] = input.value;
                        }
                    } else {
                        data[fieldKey] = input.value;
                    }
                }
            });

            // Add form-level hidden fields (required for database)
            const qualityDimensionIdInput = form.querySelector('[name="quality_dimension_id"]');
            const questionIdInput = form.querySelector('[name="question_id"]');

            if (qualityDimensionIdInput) {
                data['quality_dimension_id'] = qualityDimensionIdInput.value;
            }
            if (questionIdInput) {
                data['question_id'] = questionIdInput.value;
            }

            return data;
        },

        // Execute batch save after debounce
        executeBatchSave() {
            if (Object.keys(this.pendingSaves).length === 0) return;
            if (this.saveInProgress) {
                console.log('Save already in progress, will retry');
                return;
            }

            console.log('Executing batch save for criteria:', Object.keys(this.pendingSaves));

            // Collect criteria data
            const criteriaData = {};
            Object.keys(this.pendingSaves).forEach(title => {
                criteriaData[title] = this.pendingSaves[title].data;
            });

            // Call auto-save endpoint
            this.autoSaveCriteria(criteriaData);
        },

        // Call backend save-draft endpoint
        autoSaveCriteria(criteriaData) {
            if (this.saveInProgress) return;
            this.saveInProgress = true;

            const csrfTokenInput = document.querySelector('input[name="_csrfToken"]');
            if (!csrfTokenInput) {
                console.error('CSRF token not found');
                this.saveToLocalStorage(criteriaData);
                this.isOnline = false;

                // Show error feedback on fields
                Object.keys(this.pendingSaves).forEach(title => {
                    const pendingField = this.pendingSaves[title];
                    if (pendingField && pendingField.element) {
                        this.showFieldFeedback(pendingField.element, 'error');
                    }
                });

                this.saveInProgress = false;
                return;
            }
            const csrfToken = csrfTokenInput.value;

            fetch(`/criteria/save-draft/${this.processId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    criteria: criteriaData
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.handleSaveSuccess(criteriaData);
                } else {
                    throw new Error(data.error || 'Save failed');
                }
            })
            .catch(error => {
                console.error('Auto-save failed:', error);
                this.handleSaveError(criteriaData, error);
            })
            .finally(() => {
                this.saveInProgress = false;
            });
        },

        // Handle successful save
        handleSaveSuccess(criteriaData) {
            console.log('Save successful for criteria:', Object.keys(criteriaData));

            // Update original values (criteria are now saved)
            Object.keys(criteriaData).forEach(title => {
                // Update original values for all fields of this criterion
                const form = document.querySelector('#criterion-form');
                const escapedTitle = CSS.escape(title);
                const inputs = form.querySelectorAll(`[name^="criteria[${escapedTitle}]"]`);
                inputs.forEach(input => {
                    this.originalValues[this.extractFieldName(input.name)] = this.getFieldValue(input);
                });

                // Update saved fields timestamp
                this.savedFields[title] = {
                    timestamp: Date.now(),
                    status: 'saved'
                };

                // Show success feedback on field
                const pendingField = this.pendingSaves[title];
                if (pendingField && pendingField.element) {
                    this.showFieldFeedback(pendingField.element, 'success');
                }
            });

            // Clear pending saves
            this.pendingSaves = {};

            // Clear localStorage backup
            localStorage.removeItem(`form_process_${this.processId}_pna_${this.qualityDimensionId}_data`);

            // Update online status
            this.isOnline = true;

            // Show global success feedback
            this.showGlobalFeedback('success', '<?= __('Entwurf erfolgreich gespeichert') ?>');
        },

        // Handle save error
        handleSaveError(criteriaData, error) {
            console.error('Save error:', error);

            // Save to localStorage as backup
            this.saveToLocalStorage(criteriaData);

            // Show error feedback on fields
            Object.keys(this.pendingSaves).forEach(title => {
                const pendingField = this.pendingSaves[title];
                if (pendingField && pendingField.element) {
                    this.showFieldFeedback(pendingField.element, 'error');
                }
            });

            // Mark as offline
            this.isOnline = false;

            // Show global error feedback
            this.showGlobalFeedback('error', '<?= __('Speichern fehlgeschlagen') ?>');
        },

        // Save to localStorage as backup
        saveToLocalStorage(criteriaData) {
            const existingData = localStorage.getItem(
                `form_process_${this.processId}_pna_${this.qualityDimensionId}_data`
            );
            const parsed = existingData ? JSON.parse(existingData) : {};
            const merged = { ...parsed, ...criteriaData };

            localStorage.setItem(
                `form_process_${this.processId}_pna_${this.qualityDimensionId}_data`,
                JSON.stringify(merged)
            );
            console.log('Saved to localStorage:', Object.keys(criteriaData));
        },

        // Show field feedback
        showFieldFeedback(inputElement, status) {
            if (!inputElement) return;

            const fieldName = this.extractFieldName(inputElement.name);

            if (status === 'saving') {
                // Show saving indicator on badge
                this.showBadgeIndicator(fieldName, 'saving');
            } else if (status === 'success') {
                // Flash the fieldset border (0.5s)
                const fieldset = inputElement.closest('fieldset');
                if (fieldset) {
                    fieldset.classList.add('is-valid');
                    setTimeout(() => {
                        fieldset.classList.remove('is-valid');
                    }, 500);
                }

                // Badge checkmark (3s)
                this.showBadgeIndicator(fieldName, 'success');
            } else if (status === 'error') {
                // Error border on fieldset (persistent)
                const fieldset = inputElement.closest('fieldset');
                if (fieldset) {
                    fieldset.classList.add('is-invalid');
                }

                // Badge warning
                this.showBadgeIndicator(fieldName, 'error');
            }
        },

        // Show badge indicator on the index badge (e.g., "ND-Z14")
        showBadgeIndicator(fieldName, status) {
            // Badge has id matching the criterion key (e.g., id="ND-Z14")
            const badge = document.getElementById(fieldName);
            if (!badge) return;

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
            if (status === 'saving') {
                templateId = 'pna-autosave-saving';
            } else if (status === 'success') {
                templateId = 'pna-autosave-success';
            } else if (status === 'error') {
                templateId = 'pna-autosave-error';
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
                if (status === 'success') {
                    setTimeout(() => {
                        indicator.remove();
                    }, 3000);
                }
            }
        },

        // Handle online event
        handleOnline() {
            console.log('Coming online');
            this.isOnline = true;
            this.syncPendingChanges();
        },

        // Handle offline event
        handleOffline() {
            console.log('Going offline');
            this.isOnline = false;
            this.showOfflineIndicator();
        },

        // Show offline indicator
        showOfflineIndicator() {
            // Check if indicator already exists
            if (document.querySelector('.offline-indicator')) return;

            const indicator = document.createElement('div');
            indicator.className = 'offline-indicator';
            indicator.innerHTML = '<span style="margin-right: 0.25rem;">⚠</span>Offline - Changes saved locally';
            document.body.appendChild(indicator);
        },

        // Hide offline indicator
        hideOfflineIndicator() {
            const indicator = document.querySelector('.offline-indicator');
            if (indicator) {
                indicator.remove();
            }
        },

        // Show synced indicator
        showSyncedIndicator() {
            const indicator = document.querySelector('.offline-indicator');
            if (indicator) {
                indicator.innerHTML = '<span style="margin-right: 0.25rem;">✓</span>All synced';
                indicator.classList.add('synced');

                setTimeout(() => {
                    indicator.remove();
                }, 2000);
            }
        },

        // Sync pending changes from localStorage
        syncPendingChanges() {
            const pendingData = localStorage.getItem(
                `form_process_${this.processId}_pna_${this.qualityDimensionId}_data`
            );

            if (!pendingData) {
                this.hideOfflineIndicator();
                return;
            }

            const criteriaData = JSON.parse(pendingData);
            console.log('Syncing pending changes:', Object.keys(criteriaData));

            // Send all pending changes
            this.autoSaveCriteria(criteriaData);

            // Show synced indicator after success
            setTimeout(() => {
                if (this.isOnline) {
                    this.showSyncedIndicator();
                }
            }, 1000);
        }
    };
}
</script>
<?php $this->end(); ?>
