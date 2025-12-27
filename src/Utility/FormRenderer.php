<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\View\View;
use InvalidArgumentException;

/**
 * FormRenderer Utility
 *
 * Renders forms server-side using atomic CakePHP elements based on
 * UseCaseDescriptionConfig.json structure, replacing client-side Handlebars rendering.
 *
 * Supports the same field types and properties as the original Handlebars template:
 * - text, textarea, select, radio, file
 * - index, name, label, tooltip, help, options, rows
 */
class FormRenderer
{
    private View $view;
    private array $config;

    private string $currentLanguage;

    public function __construct(View $view)
    {
        $this->view = $view;
        $this->loadConfig();
    }

    /**
     * Load the UseCaseDescriptionConfig.json file
     */
    private function loadConfig(): void
    {
        $configPath = WWW_ROOT . 'js' . DS . 'json' . DS . 'UseCaseDescriptionConfig.json';

        if (!file_exists($configPath)) {
            throw new InvalidArgumentException("Form config file not found: {$configPath}");
        }

        $jsonContent = file_get_contents($configPath);
        $this->config = json_decode($jsonContent, true);

        $this->currentLanguage = $this->view->get('currentLanguage');

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in form config: ' . json_last_error_msg());
        }
    }

    /**
     * Render a complete form step
     *
     * @param int $step The step number (1-based)
     * @return string Rendered HTML
     */
    public function renderStep(int $step): string
    {
        $stepKey = (string)$step;

        if (!array_key_exists($stepKey, $this->config)) {
            throw new InvalidArgumentException("Step {$step} not found in form configuration");
        }

        $stepData = $this->config[$step];
        $html = '';

        // Render step header (section molecule)
        $html .= $this->view->element('molecules/form_section', [
            'category' => $stepData['category'][$this->currentLanguage] ?? '',
            'title' => $stepKey . '. ' . $stepData['title'][$this->currentLanguage] ?? '',
            'description' => $stepData['description'][$this->currentLanguage] ?? '',
        ]);

        // Render form fields
        if (isset($stepData['fields']) && is_array($stepData['fields'])) {
            foreach ($stepData['fields'] as $field) {
                $html .= $this->renderField($field);
            }
        }

        return $html;
    }

    /**
     * Render a single form field
     *
     * @param array $field Field configuration from JSON
     * @return string Rendered HTML
     */
    private function renderField(array $field): string
    {
        // Validate required field properties
        if (!isset($field['type'], $field['name'], $field['label'])) {
            throw new InvalidArgumentException('Field must have type, name, and label properties');
        }

        $fieldType = $field['type'];
        $atomElement = $this->getAtomElement($fieldType);

        // Prepare field data for the molecule wrapper
        $fieldData = [
            'index' => $field['index'] ?? null,
            'name' => $field['name'],
            'label' => $field['label'][$this->currentLanguage],
            'tooltip' => $field['tooltip'][$this->currentLanguage] ?? null,
            'help' => $field['help'][$this->currentLanguage] ?? null,
            'icon' => $field['icon'] ?? null,
            'type' => $fieldType,
            'atom_element' => $atomElement,
            'atom_data' => $this->prepareAtomData($field),
        ];

        // Render using the field molecule wrapper
        return $this->view->element('molecules/form_field', $fieldData);
    }

    /**
     * Get the appropriate atom element name for a field type
     *
     * @param string $type Field type from JSON config
     * @return string Atom element name
     */
    private function getAtomElement(string $type): string
    {
        $elementMap = [
            'text' => 'atoms/form_input',
            'textarea' => 'atoms/form_textarea',
            'select' => 'atoms/form_select',
            'radio' => 'molecules/form_radio_group',
            'file' => 'atoms/form_input',
        ];

        if (!isset($elementMap[$type])) {
            throw new InvalidArgumentException("Unsupported field type: {$type}");
        }

        return $elementMap[$type];
    }

    /**
     * Prepare data specifically for the atom element
     *
     * @param array $field Field configuration
     * @return array Data for atom element
     */
    private function prepareAtomData(array $field): array
    {
        $nestedName = 'ucd[' . $field['name'] . ']';

        $atomData = [
            'name' => $nestedName,
            'type' => $field['type'],
        ];

        switch ($field['type']) {
            case 'text':
            case 'textarea':
            case 'file':
            case 'select':
                $atomData['id'] = 'ucd_' . $field['name']; // Standard ID for single elements
                if ($field['type'] === 'file') {
                    $atomData['accept'] = $field['accept'] ?? 3;
                    $atomData['multiple'] = $field['multiple'] ?? 3;
                    $atomData['maxSize'] = $field['maxSize'] ?? 3;
                    $atomData['extensions'] = $field['extensions'] ?? 3;
                }
                if ($field['type'] === 'textarea') {
                    $atomData['rows'] = $field['rows'] ?? 3;
                }
                if (in_array($field['type'], ['select'])) {
                    $atomData['options'] = $field['options'] ?? [];
                }
                break;

            case 'radio':
                $atomData['baseId'] = 'ucd_' . $field['name']; // Base ID for radio group
                $atomData['options'] = $field['options'] ?? [];
                break;
        }

        return $atomData;
    }

    /**
     * Get the complete form configuration
     *
     * @return array Complete config array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get configuration for a specific step
     *
     * @param int $step Step number (1-based)
     * @return array Step configuration
     */
    public function getStepConfig(int $step): array
    {
        $stepKey = (string)$step;

        if (!isset($this->config[$stepKey])) {
            throw new InvalidArgumentException("Step {$step} not found in form configuration");
        }

        return $this->config[$stepKey];
    }

    /**
     * Get the maximum step number
     *
     * @return int Maximum step number
     */
    public function getMaxStep(): int
    {
        $steps = array_keys($this->config);

        return count($steps);
    }
}
