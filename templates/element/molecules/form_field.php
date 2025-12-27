<?php
/**
 * Form Field Molecule - Unified Version
 *
 * Wraps form fields with their label, tooltip, help text, and field index.
 * Supports both CakePHP raw HTML and atomic component rendering.
 *
 * Dual-mode operation:
 * 1. CakePHP Mode: When $field_html is provided, renders pre-generated form control
 * 2. Atom Mode: When $atom_element is provided, renders atomic components
 *
 * @var mixed $index Field index (e.g., 1.1, 1.2) - optional
 * @var string $name Field name
 * @var string $label Field label (optional)
 * @var bool $showLabel Whether to show the label (default: true)
 * @var string|null $labelClass Optional CSS classes for the label element (e.g., 'sr-only' for screen-reader-only)
 * @var string|null $tooltip Optional tooltip text
 * @var string|null $help Optional help text
 * @var string $type Field type (text, textarea, select, radio, checkbox)
 * @var bool $required Whether field is required (shows asterisk)
 * @var array $error_messages Validation error messages array (optional)
 * @var string|null $icon Optional icon name for input field (e.g., 'mail-01')
 * @var string|null $containerClass Optional additional CSS classes for the container element
 * @var string|null $controlClass Optional additional CSS classes for the form control (input/select/textarea)
 *
 * For CakePHP mode:
 * @var string $field_html Pre-rendered form control HTML from CakePHP Form->control()
 * @var string $field_id Field ID for label association
 *
 * For Atom mode:
 * @var string $atom_element Atom element path (e.g., 'atoms/form_input')
 * @var array $atom_data Data to pass to the atom element
 */


$index = $index ?? null;
$showLabel = $showLabel ?? true;
$labelClass = $labelClass ?? '';
$tooltip = $tooltip ?? null;
$help = $help ?? null;
$type = $type ?? 'text';
$required = $required ?? false;
$error_messages = $error_messages ?? []; // Legacy support
$client_error_messages = $client_error_messages ?? [];
$server_error_messages = $server_error_messages ?? [];
$icon = $icon ?? null;
$containerClass = $containerClass ?? '';
$controlClass = $controlClass ?? '';
$field_id = $field_id ?? $atom_data['id'] ?? strtolower(str_replace(['_', ' '], '-', $name));

// Merge for legacy support if old error_messages parameter is used
if (!empty($error_messages) && empty($client_error_messages) && empty($server_error_messages)) {
    $client_error_messages = $error_messages;
}

$hasServerErrors = !empty($server_error_messages);

// Build container classes
$containerClasses = ['mki-form-field-container', 'mb-5'];
if (!empty($containerClass)) {
    $containerClasses[] = $containerClass;
}
?>
<div class="<?= implode(' ', $containerClasses) ?>"
     x-data="{ touched: false, isInvalid: false, hasServerErrors: <?= $hasServerErrors ? 'true' : 'false' ?> }"
     @blur.capture="touched = true; isInvalid = $event.target?.validity?.valid === false; $event.target.classList.toggle('touched', true)"
     @input="hasServerErrors = false; isInvalid = $event.target?.validity?.valid === false"
     @reset-pristine.window="if ($event.detail?.fieldId === '<?= h($field_id) ?>') { touched = false; isInvalid = false; hasServerErrors = false; }"
     :class="{ 'touched': touched, 'has-server-error': hasServerErrors }">
    <div class="mki-form-field-wrapper">
        <?php if ($index !== null): ?>
            <div class="inline-flex items-baseline gap-2 mb-2">
                <span class="mki-form-field-index-badge" id="<?= h($index) ?>"><?= h($index) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($type !== 'radio'): ?>
            <?php if ($showLabel && !empty($label)): ?>
            <div class="mki-form-field-label-wrapper flex items-center justify-between <?= !empty($labelClass) && strpos($labelClass, 'sr-only') !== false ? '' : 'mb-2' ?>">
                <label for="<?= h($field_id) ?>" class="text-brand-deep font-normal text-md <?= h($labelClass) ?>">
                    <?= \App\Utility\HtmlSanitizer::sanitizeTooltip($label) ?>
                    <?php if ($required): ?>
                        <span class="text-brand-deep">*</span>
                    <?php endif; ?>
                </label>
                <?php if (!empty($tooltip)): ?>
                    <?= $this->element('atoms/tooltip', [
                        'id' => 'tooltip-' . h($field_id),
                        'content' => \App\Utility\HtmlSanitizer::sanitizeTooltip($tooltip),
                        'title' => $label ?? null,
                        'triggerLabel' => sprintf(__('Help for %s field'), $label ?? $name)
                    ]) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($showLabel && !empty($label)): ?>
            <div class="mki-form-field-label-wrapper flex items-center justify-between mb-3">
                <label class="font-medium <?= h($labelClass) ?>">
                    <?= \App\Utility\HtmlSanitizer::sanitizeTooltip($label) ?>
                    <?php if ($required): ?>
                        <span class="text-danger">*</span>
                    <?php endif; ?>
                </label>
                <?php if (!empty($tooltip)): ?>
                    <?= $this->element('atoms/tooltip', [
                        'id' => 'tooltip-' . h($field_id),
                        'content' => \App\Utility\HtmlSanitizer::sanitizeTooltip($tooltip),
                        'title' => $label ?? null,
                        'triggerLabel' => sprintf(__('Help for %s field'), $label ?? $name)
                    ]) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <fieldset class="border border-brand-light rounded-lg p-4 text-brand-deep">
        <?php endif; ?>

        <?php
        /*
         * Dual-mode rendering: CakePHP HTML or Atomic components
         */
        if (isset($field_html)):
            if ($icon && in_array($type, ['text', 'email', 'password', 'tel', 'url'])):
                ?>
                <div class="relative mki-form-field-with-icon">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <?= $this->element('atoms/icon', ['name' => $icon, 'options' => ['class' => 'w-5 h-5 text-gray-400']]) ?>
                    </div>
                    <?= $field_html ?>
                    <div x-show="(touched && isInvalid) || hasServerErrors" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <?= $this->element('atoms/icon', ['name' => 'alert-square', 'options' => ['class' => 'w-5 h-5 text-error-600']]) ?>
                    </div>
                </div>
                <?php
            else:
                ?>
                <div class="relative">
                    <?= $field_html ?>
                    <?php if (in_array($type, ['text', 'email', 'password', 'tel', 'url'])): ?>
                    <div x-show="(touched && isInvalid) || hasServerErrors" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <?= $this->element('atoms/icon', ['name' => 'alert-square', 'options' => ['class' => 'w-5 h-5 text-error-600']]) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php
            endif;
        elseif (isset($atom_element) && isset($atom_data)):
            // Merge controlClass into atom_data attributes
            if (!empty($controlClass)) {
                $atom_data['attributes'] = $atom_data['attributes'] ?? [];
                $existingClass = $atom_data['attributes']['class'] ?? '';
                $atom_data['attributes']['class'] = trim($existingClass . ' ' . $controlClass);
            }

            if ($icon && $atom_element === 'atoms/form_input'):
                ?>
                <div class="relative mki-form-field-with-icon">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <?= $this->element('atoms/icon', ['name' => $icon, 'options' => ['class' => 'w-5 h-5 text-gray-400']]) ?>
                    </div>
                    <?= $this->element($atom_element, $atom_data) ?>
                </div>
                <?php
            else:
                echo $this->element($atom_element, $atom_data);
            endif;
        endif;
        ?>

        <?php if ($type === 'radio'): ?>
            </fieldset>
        <?php endif; ?>
    </div>

    <?php if (!empty($help)): ?>
        <div class="mki-form-field-help text-sm text-gray-600 mt-2" x-show="!(touched && isInvalid) && <?= empty($server_error_messages) ? 'true' : 'false' ?>">
            <?= h($help) ?>
        </div>
    <?php endif; ?>

    <!-- Client-side validation errors (only show when HTML5 validation fails AND no server errors) -->
    <?php if (!empty($client_error_messages)): ?>
        <div class="mki-form-field-errors" x-show="touched && isInvalid && !hasServerErrors" x-cloak>
            <?php foreach ($client_error_messages as $error): ?>
                <div class="mt-1 text-sm text-error-600">
                    <?= h($error) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Server-side validation errors (show when hasServerErrors is true) -->
    <?php if (!empty($server_error_messages)): ?>
        <div class="mki-form-field-errors" x-show="hasServerErrors">
            <?php foreach ($server_error_messages as $error): ?>
                <div class="mt-1 text-sm text-error-600">
                    <?= h($error) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!--
TODO: Replace utility classes with design tokens after consolidating Figma specs
-->
