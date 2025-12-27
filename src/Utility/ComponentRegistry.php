<?php
declare(strict_types=1);

namespace App\Utility;

use RuntimeException;

/**
 * Component Registry Utility
 *
 * Manages UI component documentation and metadata from JSON configuration.
 * Provides methods to retrieve component information, examples, and parameters.
 */
class ComponentRegistry
{
    /**
     * @var array Component data from JSON
     */
    private array $components = [];

    /**
     * @var bool Whether components have been loaded
     */
    private bool $loaded = false;

    /**
     * Constructor - automatically loads components
     */
    public function __construct()
    {
        $this->loadComponents();
    }

    /**
     * Load components from JSON file
     *
     * @return void
     */
    private function loadComponents(): void
    {
        if ($this->loaded) {
            return;
        }

        $jsonPath = CONFIG . 'ui_components.json';

        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(
                    'Invalid JSON in ui_components.json: ' . json_last_error_msg(),
                );
            }

            if (!isset($data['meta'], $data['categories'], $data['showcase'])) {
                throw new RuntimeException(
                    'Invalid component registry structure: missing required keys (meta, categories, showcase)',
                );
            }

            $this->components = $data;
            $this->loaded = true;
        } else {
            $this->components = [
                'meta' => [],
                'categories' => [],
                'showcase' => ['sections' => []],
            ];
            $this->loaded = true;
        }
    }

    /**
     * Get all components data
     *
     * @return array
     */
    public function getAllComponents(): array
    {
        return $this->components;
    }

    /**
     * Get metadata about the component library
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->components['meta'] ?? [];
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->components['categories'] ?? [];
    }

    /**
     * Get components by category
     *
     * @param string $category Category name (atoms, molecules, organisms)
     * @return array
     */
    public function getComponentsByCategory(string $category): array
    {
        return $this->components['categories'][$category]['components'] ?? [];
    }

    /**
     * Get a specific component
     *
     * @param string $category Category name
     * @param string $name Component name
     * @return array Component data or empty array if not found
     */
    public function getComponent(string $category, string $name): array
    {
        return $this->components['categories'][$category]['components'][$name] ?? [];
    }

    /**
     * Get only documented components
     *
     * @return array Array of documented components grouped by category
     */
    public function getDocumentedComponents(): array
    {
        $documented = [];

        foreach ($this->components['categories'] ?? [] as $categoryKey => $category) {
            $documented[$categoryKey] = [
                'label' => $category['label'],
                'description' => $category['description'],
                'components' => [],
            ];

            foreach ($category['components'] ?? [] as $componentKey => $component) {
                if (($component['status'] ?? '') === 'documented') {
                    $documented[$categoryKey]['components'][$componentKey] = $component;
                }
            }
        }

        return $documented;
    }

    /**
     * Get pending components
     *
     * @return array Array of pending components grouped by category
     */
    public function getPendingComponents(): array
    {
        $pending = [];

        foreach ($this->components['categories'] ?? [] as $categoryKey => $category) {
            $pending[$categoryKey] = [
                'label' => $category['label'],
                'description' => $category['description'],
                'components' => [],
            ];

            foreach ($category['components'] ?? [] as $componentKey => $component) {
                if (($component['status'] ?? '') === 'pending') {
                    $pending[$categoryKey]['components'][$componentKey] = $component;
                }
            }
        }

        return $pending;
    }

    /**
     * Get showcase sections
     *
     * @return array
     */
    public function getShowcaseSections(): array
    {
        return $this->components['showcase']['sections'] ?? [];
    }

    /**
     * Get component statistics
     *
     * @return array Statistics about documented vs pending components
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => 0,
            'documented' => 0,
            'pending' => 0,
            'by_category' => [],
        ];

        foreach ($this->components['categories'] ?? [] as $categoryKey => $category) {
            $categoryStats = [
                'total' => 0,
                'documented' => 0,
                'pending' => 0,
            ];

            foreach ($category['components'] ?? [] as $component) {
                $stats['total']++;
                $categoryStats['total']++;

                if (($component['status'] ?? '') === 'documented') {
                    $stats['documented']++;
                    $categoryStats['documented']++;
                } else {
                    $stats['pending']++;
                    $categoryStats['pending']++;
                }
            }

            $stats['by_category'][$categoryKey] = $categoryStats;
        }

        return $stats;
    }

    /**
     * Check if a component exists
     *
     * @param string $category Category name
     * @param string $name Component name
     * @return bool
     */
    public function componentExists(string $category, string $name): bool
    {
        return isset($this->components['categories'][$category]['components'][$name]);
    }

    /**
     * Get component path for element rendering
     *
     * @param string $category Category name
     * @param string $name Component name
     * @return string|null Element path or null if not found
     */
    public function getComponentPath(string $category, string $name): ?string
    {
        $component = $this->getComponent($category, $name);

        return $component['path'] ?? null;
    }

    /**
     * Get component parameters
     *
     * @param string $category Category name
     * @param string $name Component name
     * @return array Parameters array or empty array if not found
     */
    public function getComponentParameters(string $category, string $name): array
    {
        $component = $this->getComponent($category, $name);

        return $component['parameters'] ?? [];
    }

    /**
     * Get component examples
     *
     * @param string $category Category name
     * @param string $name Component name
     * @return array Examples array or empty array if not found
     */
    public function getComponentExamples(string $category, string $name): array
    {
        $component = $this->getComponent($category, $name);

        return $component['examples'] ?? [];
    }

    /**
     * Search components by name or description
     *
     * @param string $query Search query
     * @return array Matching components
     */
    public function searchComponents(string $query): array
    {
        $results = [];
        $query = strtolower($query);

        foreach ($this->components['categories'] ?? [] as $categoryKey => $category) {
            foreach ($category['components'] ?? [] as $componentKey => $component) {
                $name = strtolower($component['name'] ?? '');
                $description = strtolower($component['description'] ?? '');

                if (str_contains($name, $query) || str_contains($description, $query)) {
                    $results[] = [
                        'category' => $categoryKey,
                        'key' => $componentKey,
                        'component' => $component,
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Export components as JSON string
     *
     * @param bool $prettyPrint Whether to format JSON for readability
     * @return string JSON string
     */
    public function exportAsJson(bool $prettyPrint = true): string
    {
        $options = $prettyPrint ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;

        return json_encode($this->components, $options);
    }

    /**
     * Export components as Markdown documentation
     *
     * @return string Markdown formatted documentation
     */
    public function exportAsMarkdown(): string
    {
        $markdown = '# ' . ($this->components['meta']['title'] ?? 'UI Components') . "\n\n";
        $markdown .= $this->components['meta']['description'] ?? '';
        $markdown .= "\n\n";

        foreach ($this->components['categories'] ?? [] as $categoryKey => $category) {
            $markdown .= '## ' . $category['label'] . "\n\n";
            $markdown .= $category['description'] . "\n\n";

            foreach ($category['components'] ?? [] as $componentKey => $component) {
                $markdown .= '### ' . $component['name'];

                if ($component['status'] === 'pending') {
                    $markdown .= ' ⏳';
                }

                $markdown .= "\n\n";
                $markdown .= $component['description'] . "\n\n";

                if (!empty($component['parameters'])) {
                    $markdown .= "**Parameters:**\n\n";
                    foreach ($component['parameters'] as $paramName => $param) {
                        $markdown .= '- `' . $paramName . '` ';
                        $markdown .= '(' . $param['type'] . ') ';
                        $markdown .= $param['description'];

                        if ($param['required'] ?? false) {
                            $markdown .= ' **Required**';
                        }

                        $markdown .= "\n";
                    }
                    $markdown .= "\n";
                }

                if (!empty($component['examples'])) {
                    $markdown .= "**Examples:**\n\n";
                    foreach ($component['examples'] as $example) {
                        $markdown .= '- ' . $example['title'] . "\n";
                    }
                    $markdown .= "\n";
                }
            }
        }

        return $markdown;
    }
}
