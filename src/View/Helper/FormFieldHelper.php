<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * FormField Helper
 *
 * Wraps CakePHP's Form helper to use the unified form_field molecule component.
 * Provides single source of truth for form field rendering across the application.
 */
class FormFieldHelper extends Helper
{
    /**
     * Helpers used by this helper
     */
    protected array $helpers = ['Form'];

    /**
     * Create a form control using the unified form_field molecule
     *
     * This method wraps CakePHP's Form->control() and routes it through
     * the form_field molecule to ensure consistent styling.
     *
     * @param string $fieldName Field name for Form->control()
     * @param array $options Control options including atomic-specific options
     * @return string Rendered form field HTML
     */
    public function control(string $fieldName, array $options = []): string
    {
        // Extract atomic-specific options that aren't CakePHP options
        $index = $options['index'] ?? null;
        $tooltip = $options['tooltip'] ?? null;
        $help = $options['help'] ?? null;
        $icon = $options['icon'] ?? null;
        $errorMessages = $options['error_messages'] ?? [];

        // Remove atomic options from CakePHP options
        unset($options['index'], $options['tooltip'], $options['help'], $options['icon'], $options['error_messages']);

        // Generate field ID if not provided
        $fieldId = $options['id'] ?? $this->Form->getConfig('idPrefix') . str_replace('.', '-', $fieldName);
        $options['id'] = $fieldId;

        // Get label text before disabling it
        $label = $options['label'] ?? $this->_humanize($fieldName);
        if ($label === false) {
            $label = ''; // Handle explicitly disabled labels
        }

        // Determine field type
        $type = $this->_determineFieldType($fieldName, $options);

        // Check if field is required
        $required = $options['required'] ?? false;

        // Configure minimal templates to get just the input element
        $originalTemplates = $this->Form->getTemplates();
        $this->Form->setTemplates([
            'inputContainer' => '{{content}}',
            'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}>',
            'textarea' => '<textarea name="{{name}}" {{attrs}}>{{value}}</textarea>',
            'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
            'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}" {{attrs}}>',
            'radio' => '<input type="radio" name="{{name}}" value="{{value}}" {{attrs}}>',
            'label' => '', // We handle labels in form_field
            'error' => '', // We handle errors in form_field
            'errorList' => '{{content}}',
            'errorItem' => '{{text}}',
        ]);

        // Generate the raw form control (just the input element)
        $fieldHtml = $this->Form->control($fieldName, array_merge($options, [
            'label' => false,
            'error' => false,
        ]));

        // Restore original templates
        $this->Form->setTemplates($originalTemplates);

        // Get validation errors if any (server-side)
        $serverErrors = [];
        if ($this->Form->isFieldError($fieldName)) {
            $fieldErrors = $this->Form->error($fieldName);
            if (is_array($fieldErrors)) {
                $serverErrors = $fieldErrors;
            } elseif (is_string($fieldErrors)) {
                // Extract error text from HTML if needed
                $serverErrors = [strip_tags($fieldErrors)];
            }
        }

        // Prepare data for form_field molecule
        $formFieldData = [
            'index' => $index,
            'name' => $fieldName,
            'label' => $label,
            'tooltip' => $tooltip,
            'help' => $help,
            'type' => $type,
            'required' => $required,
            'icon' => $icon,
            'field_html' => $fieldHtml,
            'field_id' => $fieldId,
            'client_error_messages' => $errorMessages,
            'server_error_messages' => $serverErrors,
        ];

        // Render using unified form_field molecule
        return $this->getView()->element('molecules/form_field', $formFieldData);
    }

    /**
     * Determine the field type for form_field molecule
     */
    private function _determineFieldType(string $fieldName, array $options): string
    {
        if (isset($options['type'])) {
            return $options['type'];
        }

        // Auto-detect based on field name
        if (str_contains($fieldName, 'password')) {
            return 'password';
        }
        if (str_contains($fieldName, 'email') || str_contains($fieldName, 'username')) {
            return 'email';
        }
        if (str_contains($fieldName, 'description') || str_contains($fieldName, 'comment')) {
            return 'textarea';
        }

        return 'text';
    }

    /**
     * Convert field name to human-readable label
     */
    private function _humanize(string $fieldName): string
    {
        // Remove prefixes and suffixes
        $fieldName = preg_replace('/^.*\./', '', $fieldName);
        $fieldName = preg_replace('/_id$/', '', $fieldName);

        // Convert to human readable
        return ucwords(str_replace(['_', '-'], ' ', $fieldName));
    }
}
