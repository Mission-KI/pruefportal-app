<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsData
 */

$title_for_layout = __('Process') . ': ' . $process->title . ' - ' . __('VCIO-Validierung abschließen');
$this->assign('title', $title_for_layout);
?>

<div class="complete-wrapper max-w-4xl mx-auto py-8">
    <?= $this->element('process_status', ['process' => $process]); ?>

    <?= $this->element('molecules/primary_card', [
        'title' => __('VCIO-Validierung abschließen'),
        'subtitle' => $process->title,
        'body' => __('Vielen Dank für die Validierung der Selbsteinstufung. Bitte überprüfen Sie Ihre Bewertungen vor dem Abschluss.')
    ]) ?>

    <div class="indicators-review-section mt-8">
        <?= $this->element('organisms/quality_dimensions_table', [
            'qualityDimensionsData' => $qualityDimensionsData,
            'showEditButtons' => true,
            'showIndicatorColumns' => true,
            'accordionMode' => true
        ]) ?>
    </div>

    <?= $this->Form->create(null, [
        'url' => ['action' => 'completeValidation', $process->id],
        '@submit.prevent' => 'validateAndSubmit($event)',
        'data-no-loading' => true,
        'data-validation-message' => __("Die VCIO-Validierung erfüllt den erfassten Schutzbedarf nicht im erforderlichen Maße."),
        'x-data' => '{
            finalConfirmation: false,
            validationErrors: [],

            validateCriteria() {
                const rows = document.querySelectorAll(\'.vcio-criterion-row\');
                const errors = [];
                const dimensionsToExpand = new Set();
                const validationMessage = this.$el.dataset.validationMessage;

                rows.forEach(row => {
                    const fulfillment = row.dataset.fulfillment;
                    const criterionIndex = row.dataset.criterionIndex;
                    const criterionName = row.dataset.criterionName;

                    // Remove any existing validation message row
                    const nextRow = row.nextElementSibling;
                    if (nextRow && nextRow.classList.contains(\'vcio-validation-message-row\')) {
                        nextRow.remove();
                    }

                    if (fulfillment === \'nein\') {
                        errors.push({
                            index: criterionIndex,
                            name: criterionName
                        });
                        row.classList.add(\'border-l-4\', \'border-error-500\', \'bg-error-50\');

                        // Extract quality dimension prefix (e.g., "DA1" -> "DA", "TR2.1" -> "TR")
                        const dimensionMatch = criterionIndex.match(/^([A-Z]+)/);
                        if (dimensionMatch) {
                            dimensionsToExpand.add(dimensionMatch[1]);
                        }

                        // Add validation message row underneath
                        const messageRow = document.createElement(\'tr\');
                        messageRow.className = \'vcio-validation-message-row bg-error-50\';

                        const messageCell = document.createElement(\'td\');
                        messageCell.setAttribute(\'colspan\', \'5\');
                        messageCell.className = \'px-6 py-2\';

                        const messageDiv = document.createElement(\'div\');
                        messageDiv.className = \'text-sm text-error-600\';
                        messageDiv.textContent = validationMessage;

                        messageCell.appendChild(messageDiv);
                        messageRow.appendChild(messageCell);
                        row.parentNode.insertBefore(messageRow, row.nextSibling);
                    } else {
                        row.classList.remove(\'border-l-4\', \'border-error-500\', \'bg-error-50\');
                    }
                });

                // Expand accordion groups containing errors
                if (dimensionsToExpand.size > 0) {
                    const accordionContainer = document.querySelector(\'.indicators-review-section [x-data]\');
                    if (accordionContainer && accordionContainer._x_dataStack) {
                        const alpineData = accordionContainer._x_dataStack[0];
                        dimensionsToExpand.forEach(dim => {
                            if (!alpineData.openDimensions.includes(dim)) {
                                alpineData.openDimensions.push(dim);
                            }
                        });
                    }
                }

                this.validationErrors = errors;
                return errors.length === 0;
            },

            validateAndSubmit(event) {
                if (!this.validateCriteria()) {
                    window.scrollTo({ top: 0, behavior: \'smooth\' });
                    return false;
                }
                // Show loading overlay before submitting
                window.dispatchEvent(new CustomEvent(\'show-loading\'));
                event.target.submit();
            }
        }'
    ]) ?>
    <div class="final-confirmation mt-8 bg-white rounded-lg shadow p-6">
        <div class="mb-4">
            <?= $this->element('atoms/form_checkbox', [
                'name' => 'final_confirmation',
                'label' => __('Die Validierung der Selbsteinstufung nach VCIO-Kriterien ist korrekt und vollständig.'),
                'attributes' => [
                    'x-model' => 'finalConfirmation'
                ]
            ]) ?>
        </div>

        <p class="text-gray-600 mb-6">
            <?= __('Bei Unklarheiten wird der Prüfling auf Sie zukommen.') ?>
        </p>

        <?= $this->Form->button(__('Validierung abschließen'), [
            'type' => 'submit',
            'class' => 'w-full bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
            'x-bind:disabled' => '!finalConfirmation'
        ]) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
