<?php
/**
 * Form Stepper Molecule
 *
 * Reusable multi-step form component with Alpine.js progressive enhancement.
 * Used by both the main form.php and form_demo.php to ensure identical form creation process.
 *
 * @var \App\View\AppView $this
 * @var int $currentStep Current step number
 * @var int $maxSteps Maximum number of steps
 * @var array $config Configuration options
 */

use App\Utility\FormRenderer;

// Default configuration
$config = array_merge([
    'demoMode' => false,
    'processId' => null,
    'usecaseDescriptionId' => null,
    'autoSaveInterval' => 120000, // 2 minutes for real, 5 seconds for demo
    'serverData' => []
], $config ?? []);

// Adjust auto-save interval for demo mode
if ($config['demoMode']) {
    $config['autoSaveInterval'] = 5000; // 5 seconds for demo
}

// Initialize FormRenderer
$formRenderer = new FormRenderer($this);
?>

<!-- Separate form for each step -->
<?php
// Entity should be passed as parameter
if (!isset($entity)) {
    throw new \InvalidArgumentException('Entity parameter is required for form_stepper element');
}

// Determine form action based on whether entity is new or existing
if ($entity->isNew()) {
    // For new entities, we need the process_id from the URL
    // This should be available in the view context
    $processId = $this->get('process')->id ?? null;
    $formAction = ['action' => 'add', $processId];
} else {
    // For existing entities, use edit with the entity ID
    $formAction = ['action' => 'edit', $entity->id];
}
?>

<?= $this->element('atoms/autosave_indicator', ['prefix' => 'ucd-autosave', 'className' => 'save-indicator']) ?>

<?php for ($stepNum = 1; $stepNum <= $maxSteps; $stepNum++): ?>
    <div class="form-content" x-show="currentStep === <?= $stepNum ?>" x-transition>
        <?= $this->Form->create($entity, [
            'url' => $formAction,
            'id' => "step-form-{$stepNum}",
            'class' => 'needs-validation',
            'enctype' => 'multipart/form-data',
            '@submit.prevent' => 'handleSubmit($event, ' . $stepNum . ')'
        ]) ?>

            <input type="hidden" name="step" value="<?= $stepNum ?>">
            <input type="hidden" name="maxSteps" value="<?= $maxSteps ?>">

            <?= $formRenderer->renderStep($stepNum) ?>

            <?php if ($stepNum < $maxSteps): ?>
                <?= $this->element('molecules/mobile_form_actions', [
                    'justify' => 'between',
                    'body' =>
                        $this->element('atoms/button', [
                            'label' => __('Reset'),
                            'variant' => 'secondary',
                            'size' => 'MD',
                            'options' => [
                                'type' => 'button',
                                '@click' => 'resetForm()'
                            ]
                        ]) .
                        $this->element('atoms/button', [
                            'label' => __('Next step'),
                            'variant' => 'primary',
                            'size' => 'MD',
                            'options' => [
                                'type' => 'submit'
                            ]
                        ])
                ]) ?>
            <?php else: ?>
                <!-- Final step completion -->
                <div class="mt-8">
                    <!-- UCD Summary Preview -->
                    <div class="mb-8">
                        <?= $this->element('molecules/usecase_description_viewer_refactored', [
                            'flatData' => $config['serverData'] ?? [],
                            'mode' => 'view',
                            'process' => $this->get('process'),
                            'usecaseDescription' => $entity,
                            'commentReferences' => [],
                            'initialOpen' => false,
                            'showEditButtons' => true
                        ]) ?>
                    </div>

                    <!-- Checkbox (centered) -->
                    <div class="flex justify-center mb-8">
                        <?= $this->element('atoms/form_checkbox', [
                            'name' => 'finalConfirmation',
                            'id' => 'checkFinishedCompletely',
                            'label' => __('The process is finished completely and correctly'),
                            'description' => __('The examiner will contact you after the review is finished.'),
                            'checked' => false,
                            'attributes' => [
                                'x-model' => 'finalConfirmation'
                            ]
                        ]) ?>
                    </div>

                    <?= $this->element('molecules/mobile_form_actions', [
                        'justify' => 'end',
                        'body' => $this->element('atoms/button', [
                            'label' => __('Accept and submit'),
                            'variant' => 'primary',
                            'size' => 'MD',
                            'options' => [
                                'type' => 'submit',
                                ':disabled' => '!finalConfirmation'
                            ]
                        ])
                    ]) ?>
                </div>
            <?php endif; ?>

        <?= $this->Form->end() ?>
    </div>
<?php endfor; ?>

<!-- Alpine.js Form Stepper Component -->
<script>
document.addEventListener('alpine:init', () => {
    // Create global store for sharing state with sidebar
    Alpine.store('formState', {
        currentStep: <?= $currentStep ?>
    });

    Alpine.data('formStepper', () => ({
        currentStep: <?= $currentStep ?>,
        maxSteps: <?= $maxSteps ?>,
        validationErrors: [],
        finalConfirmation: false,
        formData: {},
        demoMode: <?= json_encode($config['demoMode']) ?>,
        processId: <?= json_encode($config['processId']) ?>,
        usecaseDescriptionId: <?= json_encode($config['usecaseDescriptionId']) ?>,
        autoSaveInterval: <?= $config['autoSaveInterval'] ?>,
        saveInProgress: false,
        // Field change tracking
        originalValues: {},
        pendingSaves: {},
        savedFields: {},
        debounceTimer: null,
        isOnline: true,

        init() {
            // Bidirectional sync: component <-> store
            this.$watch('currentStep', (value) => {
                Alpine.store('formState').currentStep = value;
            });
            Alpine.effect(() => {
                this.currentStep = Alpine.store('formState').currentStep;
            });

            if (!this.demoMode) {
                this.loadFormData();
                this.restoreFormValues();
                this.captureOriginalValues();
                this.attachFieldListeners();

                // Online/offline detection
                window.addEventListener('online', () => this.handleOnline());
                window.addEventListener('offline', () => this.handleOffline());

                // Listen for save-draft event from sidebar button
                window.addEventListener('save-draft', () => {
                    this.saveCurrentStep();
                });

                // Listen for navigate-to-step event from sidebar navigation
                window.addEventListener('navigate-to-step', (e) => {
                    this.navigateToStep(e.detail.targetStep);
                });
            }
        },

        captureOriginalValues() {
            const form = document.querySelector(`form#step-form-${this.currentStep}`);
            if (!form) return;

            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('ucd[')) {
                    const fieldName = input.name.replace('ucd[', '').replace(']', '');
                    this.originalValues[fieldName] = this.getFieldValue(input);
                }
            });
        },

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

        attachFieldListeners() {
            const form = document.querySelector(`form#step-form-${this.currentStep}`);
            if (!form) return;

            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.name && input.name.startsWith('ucd[')) {
                    input.addEventListener('blur', (e) => this.handleFieldBlur(e));
                }
            });
        },

        handleFieldBlur(event) {
            if (this.demoMode || !this.usecaseDescriptionId) return;

            const input = event.target;
            const fieldName = input.name.replace('ucd[', '').replace(']', '');
            const currentValue = this.getFieldValue(input);
            const originalValue = this.originalValues[fieldName];

            // Only trigger save if value actually changed
            if (currentValue !== originalValue) {
                this.queueFieldSave(fieldName, currentValue, input);
            }
        },

        queueFieldSave(fieldName, value, inputElement) {
            // Add to pending saves
            this.pendingSaves[fieldName] = {
                value: value,
                element: inputElement,
                timestamp: Date.now()
            };

            // Show immediate visual feedback (optimistic UI)
            this.showBadgeIndicator(fieldName, 'pending');

            // Clear existing timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            // Set new timer (2 second debounce)
            this.debounceTimer = setTimeout(() => {
                this.executeBatchSave();
            }, 2000);
        },

        executeBatchSave() {
            if (Object.keys(this.pendingSaves).length === 0) return;
            if (this.saveInProgress) {
                // Retry after current save completes
                this.debounceTimer = setTimeout(() => {
                    this.executeBatchSave();
                }, 1000);
                return;
            }

            // Collect field data
            const ucdData = {};
            Object.keys(this.pendingSaves).forEach(fieldName => {
                ucdData[fieldName] = this.pendingSaves[fieldName].value;
            });

            // Call enhanced autoSave with specific field data
            this.autoSaveFields(ucdData);
        },

        showFieldFeedback(inputElement, status) {
            if (!inputElement) return;

            const fieldName = inputElement.name.replace('ucd[', '').replace(']', '');

            if (status === 'success') {
                // Clear any previous error state
                inputElement.classList.remove('is-invalid');

                // Show field success state (0.5s)
                inputElement.classList.add('is-valid');
                setTimeout(() => {
                    inputElement.classList.remove('is-valid');
                }, 500);

                // Show badge checkmark (10s)
                this.showBadgeIndicator(fieldName, 'success');
            } else if (status === 'error') {
                // Show field error state (persistent)
                inputElement.classList.add('is-invalid');

                // Show badge warning
                this.showBadgeIndicator(fieldName, 'error');
            } else if (status === 'saving') {
                // Optional: Show saving indicator
            }
        },

        showBadgeIndicator(fieldName, status) {
            // Find the badge for this field
            const form = document.querySelector(`form#step-form-${this.currentStep}`);
            if (!form) return;

            const input = form.querySelector(`[name="ucd[${fieldName}]"]`);
            if (!input) return;

            // Find the field wrapper that contains badge and input
            const wrapper = input.closest('.mki-form-field-wrapper');
            if (!wrapper) return;

            const badge = wrapper.querySelector('.mki-form-field-index-badge');
            if (!badge) return;

            // Find the flex container (parent of badge) or use badge's parent
            const container = badge.parentNode;
            if (!container) return;

            // Remove existing indicator
            const existingIndicator = container.querySelector('.save-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }

            // Get the appropriate icon template
            let templateId = null;
            if (status === 'pending' || status === 'saving') {
                templateId = 'ucd-autosave-saving';
            } else if (status === 'success') {
                templateId = 'ucd-autosave-success';
            } else if (status === 'error') {
                templateId = 'ucd-autosave-error';
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

        autoSaveFields(ucdData) {
            if (this.saveInProgress) {
                console.log('Save in progress, merging with pending saves');
                // Merge into pendingSaves for next batch
                Object.keys(ucdData).forEach(fieldName => {
                    this.pendingSaves[fieldName] = {
                        value: ucdData[fieldName],
                        element: document.querySelector(`[name="ucd[${fieldName}]"]`),
                        timestamp: Date.now()
                    };
                });
                // Schedule another batch save attempt
                if (this.debounceTimer) {
                    clearTimeout(this.debounceTimer);
                }
                this.debounceTimer = setTimeout(() => {
                    this.executeBatchSave();
                }, 2000);
                return;
            }
            this.saveInProgress = true;

            const csrfTokenInput = document.querySelector('input[name="_csrfToken"]');
            if (!csrfTokenInput) {
                console.error('CSRF token not found');
                this.saveToLocalStorage(ucdData);
                this.saveInProgress = false;
                return;
            }
            const csrfToken = csrfTokenInput.value;

            fetch(`/usecase-descriptions/save-draft/${this.usecaseDescriptionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    ucd: ucdData,
                    explicit_save: false
                    // Note: current_step NOT sent on auto-save
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
                    this.handleSaveSuccess(ucdData);
                } else {
                    throw new Error(data.error || 'Save failed');
                }
            })
            .catch(error => {
                console.error('Auto-save failed:', error);
                this.handleSaveError(ucdData, error);
            })
            .finally(() => {
                this.saveInProgress = false;
            });
        },

        handleSaveSuccess(ucdData) {

            // Update original values (field is now saved)
            Object.keys(ucdData).forEach(fieldName => {
                this.originalValues[fieldName] = ucdData[fieldName];

                // Update saved fields timestamp
                this.savedFields[fieldName] = {
                    timestamp: Date.now(),
                    status: 'saved'
                };

                // Show success feedback on field
                const pendingField = this.pendingSaves[fieldName];
                if (pendingField && pendingField.element) {
                    this.showFieldFeedback(pendingField.element, 'success');
                }
            });

            // Clear pending saves
            this.pendingSaves = {};

            // Check if we just came back online before clearing localStorage
            const wasOffline = document.querySelector('.offline-indicator');

            // Clear localStorage backup
            localStorage.removeItem(`form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`);

            // Update online status
            this.isOnline = true;

            // Show synced indicator if we just came back online
            if (wasOffline) {
                this.showSyncedIndicator();
            }
        },

        handleSaveError(ucdData, error) {
            console.error('Save error:', error);

            // Save to localStorage as backup
            this.saveToLocalStorage(ucdData);

            // Show error feedback on fields
            Object.keys(this.pendingSaves).forEach(fieldName => {
                const pendingField = this.pendingSaves[fieldName];
                if (pendingField && pendingField.element) {
                    this.showFieldFeedback(pendingField.element, 'error');
                }
            });

            // Mark as offline
            this.isOnline = false;
        },

        saveToLocalStorage(ucdData) {
            const existingData = localStorage.getItem(
                `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`
            );
            const parsed = existingData ? JSON.parse(existingData) : {};
            const merged = { ...parsed, ...ucdData };

            localStorage.setItem(
                `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`,
                JSON.stringify(merged)
            );
        },

        handleOffline() {
            this.isOnline = false;
            this.showOfflineIndicator();
        },

        handleOnline() {
            this.isOnline = true;
            this.syncPendingChanges();
        },

        showOfflineIndicator() {
            // Check if indicator already exists
            if (document.querySelector('.offline-indicator')) return;

            const indicator = document.createElement('div');
            indicator.className = 'offline-indicator';
            indicator.textContent = '⚠ Offline';
            document.body.appendChild(indicator);
        },

        hideOfflineIndicator() {
            const indicator = document.querySelector('.offline-indicator');
            if (indicator) {
                indicator.remove();
            }
        },

        showSyncedIndicator() {
            const indicator = document.querySelector('.offline-indicator');
            if (indicator) {
                indicator.textContent = '✓ All synced';
                indicator.classList.add('synced');

                setTimeout(() => {
                    indicator.remove();
                }, 2000);
            }
        },

        syncPendingChanges() {
            const pendingData = localStorage.getItem(
                `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`
            );

            if (!pendingData) {
                this.hideOfflineIndicator();
                return;
            }

            try {
                const ucdData = JSON.parse(pendingData);
                this.autoSaveFields(ucdData);
            } catch (e) {
                console.error('Failed to parse pending data:', e);
                localStorage.removeItem(`form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`);
                this.hideOfflineIndicator();
            }
        },

        nextStep() {
            if (this.currentStep < this.maxSteps) {
                this.currentStep++;
            }
        },

        navigateToStep(targetStep) {
            if (this.saveInProgress) {
                return; // Prevent concurrent saves
            }

            if (!this.demoMode && this.usecaseDescriptionId) {
                this.saveInProgress = true;
                this.collectFormData();

                // Use FormData to include files
                const form = document.querySelector(`form#step-form-${this.currentStep}`);
                if (!form) {
                    console.error(`Form element not found: step-form-${this.currentStep}`);
                    this.saveInProgress = false;
                    return;
                }

                const formData = new FormData(form);
                formData.append('explicit_save', 'true');
                formData.append('current_step', this.currentStep);

                // Save before navigation
                fetch(`/usecase-descriptions/save-draft/${this.usecaseDescriptionId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Clear localStorage
                        localStorage.removeItem(`form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`);

                        // Navigate by changing currentStep (no page reload needed)
                        this.currentStep = targetStep;

                        if (window.showFlash) {
                            window.showFlash('<?= __('Draft saved') ?>', 'success');
                        }
                    } else {
                        if (window.showFlash) {
                            window.showFlash('<?= __('Failed to save before navigation') ?>', 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Save failed:', error);
                    if (window.showFlash) {
                        window.showFlash('<?= __('Failed to save. Please try again.') ?>', 'error');
                    }
                    // Fallback to localStorage
                    localStorage.setItem(
                        `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`,
                        JSON.stringify(this.formData)
                    );
                })
                .finally(() => {
                    this.saveInProgress = false;
                });
            } else {
                this.currentStep = targetStep;
            }
        },

        resetForm() {
            const currentStepElement = document.querySelector(`[x-show="currentStep === ${this.currentStep}"]`);
            if (currentStepElement) {
                const inputs = currentStepElement.querySelectorAll('input, textarea, select');
                inputs.forEach((input) => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            }

            this.validationErrors = [];
            this.autoSave();
        },

        restartForm() {
            if (this.demoMode) {
                // Reset to step 1
                this.currentStep = 1;

                // Clear all form data
                this.formData = {};
                this.finalConfirmation = false;
                this.validationErrors = [];

                // Clear all form inputs across all steps
                const form = document.querySelector('form');
                if (form) {
                    form.reset();

                    // Also clear any remaining values manually
                    const allInputs = form.querySelectorAll('input, textarea, select');
                    allInputs.forEach((input) => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                }

                // Show confirmation
                if (window.showFlash) {
                    window.showFlash('<?= __('Form restarted') ?>', 'info');
                }
            }
        },

        autoSave() {
            if (!this.demoMode && this.usecaseDescriptionId) {
                this.collectFormData();

                // Prepare data for server (only text fields, no files)
                const ucdData = {};
                Object.keys(this.formData).forEach(key => {
                    if (key.startsWith('ucd[')) {
                        const fieldName = key.replace('ucd[', '').replace(']', '');
                        ucdData[fieldName] = this.formData[key];
                    }
                });

                // Get CSRF token with null check
                const csrfTokenInput = document.querySelector('input[name="_csrfToken"]');
                if (!csrfTokenInput) {
                    console.error('CSRF token not found');
                    localStorage.setItem(
                        `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`,
                        JSON.stringify(this.formData)
                    );
                    return;
                }
                const csrfToken = csrfTokenInput.value;

                // POST to server
                fetch(`/usecase-descriptions/save-draft/${this.usecaseDescriptionId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        ucd: ucdData,
                        current_step: this.currentStep,
                        explicit_save: false
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
                        // Clear localStorage on successful server save
                        localStorage.removeItem(`form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`);

                        // Show flash message
                        const time = new Date().toLocaleTimeString();
                        if (window.showFlash) {
                            window.showFlash(`<?= __('Automatically saved at') ?> ${time}`, 'success');
                        }
                    } else {
                        // Server returned error response
                        console.error('Auto-save failed:', data.error);
                        // Fallback to localStorage
                        localStorage.setItem(
                            `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`,
                            JSON.stringify(this.formData)
                        );
                    }
                })
                .catch(error => {
                    console.error('Auto-save failed:', error);
                    // Fallback to localStorage on error
                    localStorage.setItem(
                        `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`,
                        JSON.stringify(this.formData)
                    );
                });
            }
        },

        saveCurrentStep() {
            if (this.saveInProgress) {
                return; // Prevent concurrent saves
            }

            if (!this.demoMode && this.usecaseDescriptionId) {
                this.saveInProgress = true;
                this.collectFormData();

                // Use FormData to include files
                const form = document.querySelector(`form#step-form-${this.currentStep}`);
                if (!form) {
                    console.error(`Form element not found: step-form-${this.currentStep}`);
                    if (window.showFlash) {
                        window.showFlash('<?= __('Form error. Please refresh the page.') ?>', 'error');
                    }
                    this.saveInProgress = false;
                    return;
                }

                const formData = new FormData(form);
                formData.append('explicit_save', 'true');
                formData.append('current_step', this.currentStep);

                // POST to server
                fetch(`/usecase-descriptions/save-draft/${this.usecaseDescriptionId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Clear localStorage
                        localStorage.removeItem(`form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`);

                        // Show flash message
                        if (window.showFlash) {
                            window.showFlash('<?= __('Entwurf erfolgreich gespeichert') ?>', 'success');
                        }
                    } else {
                        if (window.showFlash) {
                            window.showFlash('<?= __('Entwurf konnte nicht gespeichert werden') ?>', 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Save failed:', error);
                    if (window.showFlash) {
                        window.showFlash('<?= __('Entwurf konnte nicht gespeichert werden. Bitte überprüfen Sie Ihre Internetverbindung.') ?>', 'error');
                    }
                    // Fallback to localStorage
                    localStorage.setItem(
                        `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`,
                        JSON.stringify(this.formData)
                    );
                })
                .finally(() => {
                    this.saveInProgress = false;
                });
            }
        },

        loadFormData() {
            const pendingKey = `form_process_${this.processId}_ucd_${this.usecaseDescriptionId}_data`;

            // Auto-sync pending changes on page load
            if (!this.demoMode && this.usecaseDescriptionId) {
                const pendingData = localStorage.getItem(pendingKey);

                if (pendingData) {
                    try {
                        const ucdData = JSON.parse(pendingData);
                        this.autoSaveFields(ucdData);
                        // autoSaveFields will clear localStorage on success
                        return; // Skip form loading - sync handles the data
                    } catch (e) {
                        console.error('Failed to parse pending data:', e);
                        // Fall through to normal form loading
                    }
                }
            }

            // Load form data from localStorage (only if no sync happened)
            if (!this.demoMode) {
                const savedData = localStorage.getItem(pendingKey);

                if (savedData) {
                    try {
                        this.formData = JSON.parse(savedData);
                    } catch (e) {
                        console.error('Failed to parse saved form data:', e);
                    }
                }

                <?php if (!$config['demoMode'] && !empty($config['serverData'])): ?>
                    const serverData = <?= json_encode($config['serverData']) ?>;
                    this.formData = { ...this.formData, ...serverData };
                <?php endif; ?>
            }
        },

        collectFormData() {
            const form = document.querySelector('form');
            if (form) {
                // Only collect data from the currently visible step
                const currentStepElement = document.querySelector(`[x-show="currentStep === ${this.currentStep}"]`);

                if (!currentStepElement) {
                    return;
                }

                this.formData = {};

                // Collect all inputs from current step only
                const inputs = currentStepElement.querySelectorAll('input:not([type="file"]), textarea, select');
                inputs.forEach((input) => {
                    if (input.name && input.name !== '_csrfToken') {
                        if (input.type === 'checkbox') {
                            this.formData[input.name] = input.checked;
                        } else if (input.type === 'radio') {
                            if (input.checked) {
                                this.formData[input.name] = input.value;
                            }
                        } else if (input.value !== '') {
                            this.formData[input.name] = input.value;
                        }
                    }
                });

                // Note: File inputs are handled separately during form submission
                // We don't store them in formData for localStorage
            }
        },

        restoreFormValues() {
            Object.keys(this.formData).forEach((key) => {
                if (key === '_csrfToken') {
                    return;
                }

                // Try with original key first, then try with ucd[] wrapper
                let element = document.querySelector(`[name="${key}"]`);
                if (!element && !key.startsWith('ucd[')) {
                    element = document.querySelector(`[name="ucd[${key}]"]`);
                }

                if (element) {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = element.value === this.formData[key];
                    } else {
                        element.value = this.formData[key];
                    }
                }
            });
        },

        handleSubmit(event, stepNum) {
            if (this.demoMode) {
                this.collectFormData();
                alert('Form submitted successfully! (Demo only)');
                this.resetForm();
            } else {
                // Check final confirmation for last step
                if (stepNum === this.maxSteps && !this.finalConfirmation) {
                    alert('Please confirm that the process is finished completely and correctly.');
                    return;
                }

                // Each form only contains its own step's fields
                // So we can just submit it normally!
                event.target.submit();
            }
        }
    }));
});
</script>
