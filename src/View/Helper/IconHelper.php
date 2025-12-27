<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Utility\FileIcon;
use App\Utility\Icon;
use BackedEnum;
use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * Icon Helper
 *
 * Provides methods for working with SVG icons in the application.
 * Handles loading, caching, and processing of SVG files.
 */
class IconHelper extends Helper
{
    /**
     * Cache for loaded SVG content
     *
     * @var array<string, string>
     */
    protected static array $svgCache = [];

    /**
     * Path to icons directory
     *
     * @var string
     */
    protected string $iconsPath;

    /**
     * Initialize the helper
     *
     * @param array $config Configuration options
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->iconsPath = WWW_ROOT . 'icons' . DS;
    }

    /**
     * Get SVG content for an icon
     *
     * @param \App\Utility\Icon|\App\Utility\FileIcon|string $name Icon enum or name
     * @return string Processed SVG content or error message
     */
    public function getSvg(Icon|FileIcon|string $name): string
    {
        // Convert enum to string value
        $iconName = $name instanceof BackedEnum ? $name->value : $name;

        // Handle simple identifiers (e.g., 'file-pdf')
        if (is_string($iconName) && str_starts_with($iconName, 'file-')) {
            $iconName = $this->resolveIdentifier($iconName);
        }

        // Check cache first
        if (isset(self::$svgCache[$iconName])) {
            return self::$svgCache[$iconName];
        }

        // Determine which directory to check
        $filePath = $this->resolveIconPath($iconName, $name);

        if (!file_exists($filePath)) {
            $error = Configure::read('debug')
                ? '<span class="text-red-500">[Icon not found: ' . h($iconName) . ']</span>'
                : '';
            self::$svgCache[$iconName] = $error;

            return $error;
        }

        // Load and process SVG
        $svgContent = file_get_contents($filePath);

        if ($svgContent === false) {
            $error = Configure::read('debug')
                ? '<span class="text-red-500">[Error loading icon: ' . h($iconName) . ']</span>'
                : '';
            self::$svgCache[$iconName] = $error;

            return $error;
        }

        // Process SVG to use currentColor
        $svgContent = $this->processSvg($svgContent, $iconName);

        // Cache the processed content
        self::$svgCache[$iconName] = $svgContent;

        return $svgContent;
    }

    /**
     * Resolve simple identifier to FileIcon enum value
     *
     * @param string $identifier Simple identifier like 'file-pdf'
     * @return string Icon filename
     */
    protected function resolveIdentifier(string $identifier): string
    {
        $mappings = require CONFIG . 'file_icon_mappings.php';

        if (isset($mappings['identifiers'][$identifier])) {
            $caseName = $mappings['identifiers'][$identifier];
            // Use constant() to get the enum case by name
            $enumCase = constant(FileIcon::class . '::' . $caseName);

            return $enumCase->value;
        }

        // Check if it exists as a regular icon before warning
        $regularIconPath = $this->iconsPath . $identifier . '.svg';
        if (!file_exists($regularIconPath)) {
            // Unknown identifier and doesn't exist as regular icon
            if (Configure::read('debug')) {
                trigger_error("Unknown file icon identifier: {$identifier}", E_USER_WARNING);
            }
        }

        return $identifier;
    }

    /**
     * Resolve icon file path based on type
     *
     * @param string $iconName Icon filename
     * @param \App\Utility\Icon|\App\Utility\FileIcon|string $original Original input
     * @return string Full path to icon file
     */
    protected function resolveIconPath(string $iconName, Icon|FileIcon|string $original): string
    {
        // If original was FileIcon enum, look in file-icons/
        if ($original instanceof FileIcon) {
            return $this->iconsPath . 'file-icons' . DS . $iconName . '.svg';
        }

        // If original was Icon enum, look in icons/
        if ($original instanceof Icon) {
            return $this->iconsPath . $iconName . '.svg';
        }

        // For strings, try icons/ first, then file-icons/
        $mainPath = $this->iconsPath . $iconName . '.svg';
        if (file_exists($mainPath)) {
            return $mainPath;
        }

        return $this->iconsPath . 'file-icons' . DS . $iconName . '.svg';
    }

    /**
     * Process SVG content to use currentColor
     *
     * @param string $svg Raw SVG content
     * @param string $name Icon name for special handling
     * @return string Processed SVG content
     */
    protected function processSvg(string $svg, string $name = ''): string
    {
        // Skip color replacement for step icons - they already use currentColor appropriately
        $preserveColorIcons = ['step-complete', 'step-current', 'step-incomplete'];

        if (!in_array($name, $preserveColorIcons)) {
            // Replace hardcoded colors with currentColor
            $replacements = [
                // Stroke colors
                'stroke="black"' => 'stroke="currentColor"',
                'stroke="#000000"' => 'stroke="currentColor"',
                'stroke="#000"' => 'stroke="currentColor"',
                'stroke="rgb(0,0,0)"' => 'stroke="currentColor"',
                'stroke="rgb(0, 0, 0)"' => 'stroke="currentColor"',
                'stroke="#3C0483"' => 'stroke="currentColor"',  // Quality dimension icons purple stroke

                // Fill colors (but not fill="none")
                'fill="black"' => 'fill="currentColor"',
                'fill="#000000"' => 'fill="currentColor"',
                'fill="#000"' => 'fill="currentColor"',
                'fill="rgb(0,0,0)"' => 'fill="currentColor"',
                'fill="rgb(0, 0, 0)"' => 'fill="currentColor"',
                'fill="#3C0483"' => 'fill="currentColor"',  // Quality dimension icons purple fill
            ];

            $svg = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $svg,
            );
        }

        // Remove width and height attributes from SVG element only (not from child elements)
        $svg = preg_replace('/<svg([^>]*?)\s+(width|height)="[^"]*"/', '<svg$1', $svg);
        $svg = preg_replace('/<svg([^>]*?)\s+(width|height)="[^"]*"/', '<svg$1', $svg); // Run twice for both attrs

        // Fix clipPath ID conflicts by making them unique
        if (preg_match_all('/id="(clip[^"]*)"/', $svg, $matches)) {
            foreach ($matches[1] as $originalId) {
                $uniqueId = $originalId . '_' . uniqid();
                $svg = str_replace(
                    [
                        'id="' . $originalId . '"',
                        'clip-path="url(#' . $originalId . ')"',
                    ],
                    [
                        'id="' . $uniqueId . '"',
                        'clip-path="url(#' . $uniqueId . ')"',
                    ],
                    $svg,
                );
            }
        }

        // Ensure viewBox is present for proper scaling
        if (strpos($svg, 'viewBox') === false) {
            // Try to extract dimensions and add viewBox
            if (preg_match('/width="(\d+)".*height="(\d+)"/', $svg, $matches)) {
                $width = $matches[1];
                $height = $matches[2];
                $svg = str_replace('<svg', '<svg viewBox="0 0 ' . $width . ' ' . $height . '"', $svg);
            } else {
                // Default viewBox if dimensions not found
                $svg = str_replace('<svg', '<svg viewBox="0 0 24 24"', $svg);
            }
        }

        return $svg;
    }

    /**
     * Get list of all available icons
     *
     * @return array<string> Array of icon names (without .svg extension)
     */
    public function getAvailableIcons(): array
    {
        $icons = [];

        if (is_dir($this->iconsPath)) {
            $files = scandir($this->iconsPath);

            foreach ($files as $file) {
                if (substr($file, -4) === '.svg') {
                    $icons[] = substr($file, 0, -4);
                }
            }
        }

        sort($icons);

        return $icons;
    }

    /**
     * Check if an icon exists
     *
     * @param string $name Icon name without .svg extension
     * @return bool True if icon exists
     */
    public function exists(string $name): bool
    {
        return file_exists($this->iconsPath . $name . '.svg');
    }

    /**
     * Clear the SVG cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        self::$svgCache = [];
    }

    /**
     * Get icon with wrapper element
     *
     * This is a convenience method that returns the icon wrapped in a span
     * with the appropriate classes.
     *
     * @param string $name Icon name
     * @param array $options Options including 'size', 'class', etc.
     * @return string HTML for the icon
     */
    public function render(string $name, array $options = []): string
    {
        $size = $options['size'] ?? 'md';
        $classes = ['icon', 'icon-' . $size];

        // Add animation classes
        if (!empty($options['spin'])) {
            $classes[] = 'icon-spin';
        }
        if (!empty($options['pulse'])) {
            $classes[] = 'icon-pulse';
        }
        if (!empty($options['bounce'])) {
            $classes[] = 'icon-bounce';
        }

        // Add custom classes
        if (!empty($options['class'])) {
            $customClasses = is_array($options['class']) ? $options['class'] : [$options['class']];
            $classes = array_merge($classes, $customClasses);
        }

        $svg = $this->getSvg($name);

        $attributes = [
            'class' => implode(' ', $classes),
        ];

        // Add other HTML attributes
        foreach ($options as $key => $value) {
            if (!in_array($key, ['size', 'class', 'spin', 'pulse', 'bounce'])) {
                $attributes[$key] = $value;
            }
        }

        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= ' ' . h($key) . '="' . h($value) . '"';
        }

        return '<span' . $attributeString . '>' . $svg . '</span>';
    }
}
