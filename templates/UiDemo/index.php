<?php
/**
 * UI Demo Catalog
 *
 * Live examples of molecules and organisms built with the atomic design system.
 * Shows different variants and configurations of each component.
 *
 * TODO: Refactor this file - break layout into separate templates:
 * - Move main layout/container to a layout file
 * - Create partial templates for each component type's special rendering
 * - Extract parameters section, code accordion, and usage section into shared partials
 * - Keep only the routing/orchestration logic in this file
 * This will make the data-driven approach clearer and more maintainable.
 *
 * @var \App\View\AppView $this
 */

use App\Utility\ComponentRegistry;
use App\Utility\Icon;
use App\Utility\FileIcon;

$this->assign('title', __('UI Component Demo'));

// Initialize component registry
$registry = new ComponentRegistry();
$meta = $registry->getMeta();
$showcase = $registry->getShowcaseSections();

// Capture the component sections
ob_start();
?>

    <!-- Component Sections -->
    <div class="space-y-16">

        <?php foreach ($showcase as $section): ?>
            <?php
            // Get component data for this section
            $component = $registry->getComponent($section['category'], $section['component']);
            ?>

            <!-- Component Section -->
            <section id="<?= $section['id'] ?>" class="scroll-mt-20">
                <!-- Section Header -->
                <header class="ui-demo-section-header">
                    <div class="flex items-center mb-3">
                        <span class="ui-demo-category-badge mr-3">
                            <?= ucfirst($section['category']) ?>
                        </span>
                    </div>
                    <h2 class="ui-demo-section-title">
                        <?= h($section['title']) ?>
                    </h2>
                    <?php if (!empty($component['description'])): ?>
                        <p class="ui-demo-section-description"><?= h($component['description']) ?></p>
                    <?php endif; ?>
                </header>

                <!-- Parameters Section (Accordion) -->
                <?php if (!empty($component['parameters'])): ?>
                    <?php
                    ob_start();
                    ?>
                    <div class="space-y-4">
                        <?php foreach ($component['parameters'] as $paramName => $param): ?>
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-start">
                                    <code class="bg-white px-3 py-1 rounded text-blue-600 font-mono font-semibold"><?= h($paramName) ?></code>
                                    <span class="ml-3 text-gray-600">
                                        <span class="font-medium"><?= h($param['type']) ?></span>
                                        <?php if ($param['required'] ?? false): ?>
                                            <span class="text-red-500 ml-1">*Required</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="mt-2 text-gray-700"><?= h($param['description']) ?></p>
                                <?php if (!empty($param['options'])): ?>
                                    <div class="mt-2">
                                        <span class="text-sm text-gray-500">Options: </span>
                                        <?php foreach ($param['options'] as $option): ?>
                                            <code class="text-xs bg-white px-2 py-0.5 rounded text-gray-600 mr-1"><?= h($option) ?></code>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($param['default'])): ?>
                                    <div class="mt-1">
                                        <span class="text-sm text-gray-500">Default: </span>
                                        <code class="text-xs bg-white px-2 py-0.5 rounded text-gray-600"><?= h($param['default']) ?></code>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    $parametersContent = ob_get_clean();
                    ?>

                    <div class="mb-8">
                        <?= $this->element('atoms/accordion_item', [
                            'id' => $section['id'] . '-parameters',
                            'title' => 'Parameters & Configuration',
                            'content' => $parametersContent,
                            'open' => false,
                            'escape' => false
                        ]) ?>
                    </div>
                <?php endif; ?>

                <!-- Dynamic Usage Section from JSON -->
                <?php if (!empty($component['usage'])): ?>
                    <?php
                    // Determine color scheme based on component type
                    $colorScheme = [
                        'modal' => ['bg' => 'blue', 'text' => 'blue'],
                        'button' => ['bg' => 'green', 'text' => 'green'],
                        'default' => ['bg' => 'gray', 'text' => 'gray']
                    ];
                    $colors = $colorScheme[$section['component']] ?? $colorScheme['default'];
                    ?>
                    <div class="mb-8 p-6 bg-<?= $colors['bg'] ?>-50 border border-<?= $colors['bg'] ?>-200 rounded-lg">
                        <h3 class="text-xl font-semibold text-<?= $colors['text'] ?>-900 mb-4">Usage: How to Use <?= h(ucfirst($section['component'])) ?>s</h3>
                        <div class="space-y-4 text-sm">
                            <?php foreach ($component['usage'] as $sectionKey => $usageSection): ?>
                                <?php if ($sectionKey === 'important_notes'): ?>
                                    <!-- Important Notes -->
                                    <?php foreach ($usageSection as $note): ?>
                                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                            <p class="text-yellow-800"><strong>Important:</strong> <?= h($note) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php elseif ($sectionKey === 'note'): ?>
                                    <!-- Single Note -->
                                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                        <p class="text-yellow-800"><strong>Note:</strong> <?= h($usageSection) ?></p>
                                    </div>
                                <?php elseif (isset($usageSection['code']) && !isset($usageSection['methods'])): ?>
                                    <!-- Simple code section -->
                                    <div>
                                        <p class="font-semibold text-<?= $colors['text'] ?>-800 mb-2"><?= h($usageSection['title']) ?>:</p>
                                        <pre class="bg-white px-4 py-3 rounded border border-<?= $colors['bg'] ?>-200 overflow-x-auto"><code><?= h($usageSection['code']) ?></code></pre>
                                    </div>
                                <?php elseif (isset($usageSection['methods'])): ?>
                                    <!-- Methods section -->
                                    <div>
                                        <p class="font-semibold text-<?= $colors['text'] ?>-800 mb-2"><?= h($usageSection['title']) ?>:</p>
                                        <div class="space-y-3">
                                            <?php foreach ($usageSection['methods'] as $method): ?>
                                                <div class="bg-white p-3 rounded border border-<?= $colors['bg'] ?>-200">
                                                    <p class="font-medium text-<?= $colors['text'] ?>-700"><?= h($method['title']) ?></p>
                                                    <code class="block mt-1 text-xs"><?= h($method['code']) ?></code>
                                                    <?php if (!empty($method['note'])): ?>
                                                        <p class="text-xs text-<?= $colors['text'] ?>-600 mt-1"><?= h($method['note']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php elseif (isset($usageSection['examples'])): ?>
                                    <!-- Examples section -->
                                    <div>
                                        <p class="font-semibold text-<?= $colors['text'] ?>-800 mb-2"><?= h($usageSection['title']) ?>:</p>
                                        <div class="space-y-3">
                                            <?php foreach ($usageSection['examples'] as $example): ?>
                                                <div class="bg-white p-3 rounded border border-<?= $colors['bg'] ?>-200">
                                                    <p class="font-medium text-<?= $colors['text'] ?>-700"><?= h($example['title']) ?></p>
                                                    <code class="block mt-1 text-xs"><?= h($example['code']) ?></code>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Examples Section -->
                <?php if (!empty($component['examples'])): ?>
                    <div class="space-y-12">
                        <h3 class="text-2xl font-semibold text-gray-900 border-b border-gray-200 pb-3">Examples</h3>

                        <?php foreach ($component['examples'] as $index => $example): ?>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <!-- Example Header -->
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                    <h4 class="text-xl font-semibold text-gray-800"><?= h($example['title']) ?></h4>
                                </div>

                                <!-- Live Example -->
                                <div class="p-6 bg-white">
                                    <?php if ($section['component'] === 'icon' && isset($example['params']['iconEnumExamples'])): ?>
                                        <!-- Icon Enum Examples -->
                                        <div class="space-y-4">
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                                <div class="flex items-start">
                                                    <?= $this->element('atoms/icon', [
                                                        'name' => 'info-circle',
                                                        'size' => 'sm',
                                                        'options' => ['class' => 'text-blue-600 mr-2 mt-0.5']
                                                    ]) ?>
                                                    <div>
                                                        <p class="text-blue-900 font-semibold mb-1">Recommended: Use Icon Enum</p>
                                                        <p class="text-blue-800 text-sm">
                                                            Import <code class="bg-white px-1.5 py-0.5 rounded">use App\Utility\Icon;</code> for autocomplete and type safety.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                                <?php foreach ($example['params']['iconEnumExamples'] as $iconExample):
                                                    // Parse the enum constant (e.g., "Icon::HOME")
                                                    $enumParts = explode('::', $iconExample['enum']);
                                                    if (count($enumParts) === 2 && $enumParts[0] === 'Icon') {
                                                        $iconEnum = constant('\App\Utility\Icon::' . $enumParts[1]);
                                                ?>
                                                    <div class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                                        <?= $this->element('atoms/icon', ['name' => $iconEnum, 'size' => 'xl']) ?>
                                                        <span class="mt-2 text-sm text-gray-700 text-center font-medium"><?= h($iconExample['label']) ?></span>
                                                        <code class="mt-1 text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded"><?= h($iconExample['enum']) ?></code>
                                                    </div>
                                                <?php
                                                    }
                                                endforeach;
                                                ?>
                                            </div>
                                        </div>
                                    <?php elseif ($section['component'] === 'icon' && isset($example['params']['fileIconExamples'])): ?>
                                        <!-- FileIcon Enum Examples -->
                                        <div class="space-y-4">
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                                <div class="flex items-start">
                                                    <?= $this->element('atoms/icon', [
                                                        'name' => 'info-circle',
                                                        'size' => 'sm',
                                                        'options' => ['class' => 'text-green-600 mr-2 mt-0.5']
                                                    ]) ?>
                                                    <div>
                                                        <p class="text-green-900 font-semibold mb-1">File Type Icons: Use FileIcon Enum</p>
                                                        <p class="text-green-800 text-sm">
                                                            Import <code class="bg-white px-1.5 py-0.5 rounded">use App\Utility\FileIcon;</code> for file type icons.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                                <?php foreach ($example['params']['fileIconExamples'] as $iconExample):
                                                    // Parse the enum constant (e.g., "FileIcon::PDF")
                                                    $enumParts = explode('::', $iconExample['enum']);
                                                    if (count($enumParts) === 2 && $enumParts[0] === 'FileIcon') {
                                                        $iconEnum = constant('\App\Utility\FileIcon::' . $enumParts[1]);
                                                ?>
                                                    <div class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                                        <?= $this->element('atoms/icon', ['name' => $iconEnum, 'size' => 'xl']) ?>
                                                        <span class="mt-2 text-sm text-gray-700 text-center font-medium"><?= h($iconExample['label']) ?></span>
                                                        <code class="mt-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded"><?= h($iconExample['enum']) ?></code>
                                                    </div>
                                                <?php
                                                    }
                                                endforeach;
                                                ?>
                                            </div>
                                        </div>
                                    <?php elseif ($section['component'] === 'icon' && isset($example['params']['simpleIdentifierExamples'])): ?>
                                        <!-- Simple Identifier Examples -->
                                        <div class="space-y-4">
                                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                                                <div class="flex items-start">
                                                    <?= $this->element('atoms/icon', [
                                                        'name' => 'info-circle',
                                                        'size' => 'sm',
                                                        'options' => ['class' => 'text-purple-600 mr-2 mt-0.5']
                                                    ]) ?>
                                                    <div>
                                                        <p class="text-purple-900 font-semibold mb-1">Simple Identifiers: Quick & Easy</p>
                                                        <p class="text-purple-800 text-sm">
                                                            Use simple string identifiers like <code class="bg-white px-1.5 py-0.5 rounded">'file-pdf'</code> for convenience.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                                <?php foreach ($example['params']['simpleIdentifierExamples'] as $iconExample): ?>
                                                    <div class="flex flex-col items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                                        <?= $this->element('atoms/icon', ['name' => $iconExample['identifier'], 'size' => 'xl']) ?>
                                                        <span class="mt-2 text-sm text-gray-700 text-center font-medium"><?= h($iconExample['label']) ?></span>
                                                        <code class="mt-1 text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded">'<?= h($iconExample['identifier']) ?>'</code>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php elseif ($section['component'] === 'icon' && isset($example['params']['categorizedIcons'])): ?>
                                        <!-- Categorized Icon Library -->
                                        <div class="space-y-8">
                                            <?php foreach ($example['params']['categorizedIcons'] as $categoryName => $icons): ?>
                                                <!-- Category Header -->
                                                <div class="border-t border-gray-300 pt-6 first:border-t-0 first:pt-0">
                                                    <h5 class="text-lg font-semibold text-gray-800 mb-4"><?= h($categoryName) ?></h5>
                                                    <!-- Icon Grid -->
                                                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-4">
                                                        <?php foreach ($icons as $icon): ?>
                                                            <div class="flex flex-col items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                                                <?= $this->element($component['path'], ['name' => $icon['name'], 'size' => 'lg']) ?>
                                                                <span class="mt-2 text-xs text-gray-600 text-center font-mono"><?= h($icon['label']) ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($section['component'] === 'icon' && isset($example['params']['coloredIcons'])): ?>
                                        <!-- Colored Icon Examples -->
                                        <div class="flex flex-wrap items-center gap-8">
                                            <?php foreach ($example['params']['coloredIcons'] as $coloredIcon): ?>
                                                <div class="flex flex-col items-center">
                                                    <?= $this->element($component['path'], [
                                                        'name' => $coloredIcon['name'],
                                                        'size' => 'xl',
                                                        'options' => ['class' => $coloredIcon['color']]
                                                    ]) ?>
                                                    <span class="mt-2 text-sm text-gray-600 text-center"><?= h($coloredIcon['label']) ?></span>
                                                    <code class="text-xs text-gray-500 mt-1"><?= h($coloredIcon['color']) ?></code>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($section['component'] === 'icon' && isset($example['params']['animatedIcons'])): ?>
                                        <!-- Animated Icon Examples -->
                                        <div class="flex flex-wrap items-center gap-8">
                                            <?php foreach ($example['params']['animatedIcons'] as $animIcon): ?>
                                                <div class="flex flex-col items-center">
                                                    <?= $this->element($component['path'], [
                                                        'name' => $animIcon['name'],
                                                        'size' => 'xl',
                                                        $animIcon['animation'] => true
                                                    ]) ?>
                                                    <span class="mt-2 text-sm text-gray-600 text-center"><?= h($animIcon['label']) ?></span>
                                                    <code class="text-xs text-gray-500 mt-1"><?= h($animIcon['animation']) ?>: true</code>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($section['component'] === 'card' && isset($example['params']['variants'])): ?>
                                        <!-- Card Color Variants -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                            <?php foreach ($example['params']['variants'] as $variant): ?>
                                                <div class="w-full">
                                                    <?= $this->element($component['path'], [
                                                        'title' => $variant['title'],
                                                        'body' => $variant['body'],
                                                        'variant' => $variant['variant']
                                                    ]) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($section['component'] === 'modal' && !empty($example['params']['show_reference_demo'])): ?>
                                        <!-- Reference System Demo -->
                                        <?php
                                        // Sample fields with references
                                        $referenceFields = [
                                            ['id' => 'ref_field_1', 'badge' => '1.1', 'title' => 'Use Case Title', 'desc' => 'AI-powered quality assessment system'],
                                            ['id' => 'ref_field_2', 'badge' => '1.2', 'title' => 'Use Case Description', 'desc' => 'The system analyzes AI models for compliance...'],
                                            ['id' => 'ref_field_3', 'badge' => '2.1', 'title' => 'Target Users', 'desc' => 'Quality assurance teams and auditors']
                                        ];
                                        ?>
                                        <div class="space-y-4">
                                            <?php foreach ($referenceFields as $field): ?>
                                                <div class="border-b border-gray-200 pb-3">
                                                    <div class="flex items-start gap-3" id="<?= $field['id'] ?>">
                                                        <span class="inline-block bg-blue-600 text-white text-sm font-semibold px-3 py-1 rounded" data-reference="<?= $field['id'] ?>"><?= h($field['badge']) ?></span>
                                                        <div class="flex-1">
                                                            <h5 class="font-semibold"><?= h($field['title']) ?></h5>
                                                            <p class="text-gray-600 text-sm"><?= h($field['desc']) ?></p>
                                                            <button
                                                                data-modal-trigger="demo-reference-modal"
                                                                data-reference-id="<?= $field['id'] ?>"
                                                                class="mt-2 text-brand hover:text-brand-dark text-sm underline"
                                                            >
                                                                Add Comment
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php
                                        $referenceFormContent = '<form action="#" method="post" class="js-ajax-form">
                                            <div class="space-y-4">
                                                <div>
                                                    <label for="reference-select" class="block text-sm font-medium text-gray-700 mb-1">Reference Field</label>
                                                    <select id="jsReferences" name="reference_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                                                        <option value="">General</option>
                                                    </select>
                                                    <p class="mt-1 text-xs text-gray-500">Auto-populated with page references</p>
                                                </div>
                                                <div>
                                                    <label for="comment-content" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                                                    <textarea id="comment-content" name="content" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Enter your comment..."></textarea>
                                                </div>
                                            </div>
                                            <div class="mt-6 flex justify-end gap-3">
                                                <button type="button" @click="window.dispatchEvent(new CustomEvent(\'close-modal-demo-reference-modal\'))" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-dark transition-colors">Submit Comment</button>
                                            </div>
                                        </form>';
                                        echo $this->element('molecules/modal', [
                                            'id' => 'demo-reference-modal',
                                            'title' => 'Add Comment',
                                            'content' => $referenceFormContent,
                                            'escape' => false,
                                            'size' => 'lg'
                                        ]);
                                        ?>
                                    <?php elseif ($section['component'] === 'modal'): ?>
                                        <!-- Modal Component -->
                                        <?= $this->element($component['path'], $example['params']) ?>
                                        <button
                                            data-modal-trigger="<?= h($example['params']['id']) ?>"
                                            class="mt-4 px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-dark transition-colors"
                                        >
                                            Open Modal
                                        </button>
                                    <?php elseif ($section['component'] === 'flash' && isset($example['params']['interactive'])): ?>
                                        <!-- Interactive Flash Examples with Buttons (keeping hardcoded for now) -->
                                        <div class="space-y-6">
                                            <div id="flash-container" class="min-h-[100px]"></div>

                                            <div class="flex flex-wrap gap-3">
                                                <?php foreach ($example['params']['types'] as $type): ?>
                                                    <?php
                                                    $messages = [
                                                        'success' => 'The criteria has been saved successfully!',
                                                        'error' => 'The User could not be saved. Please, try again.',
                                                        'warning' => 'This action may have unintended consequences.',
                                                        'info' => 'Your session will expire in 10 minutes.'
                                                    ];
                                                    ?>
                                                    <?= $this->element('atoms/button', [
                                                        'label' => 'Show ' . ucfirst($type),
                                                        'variant' => $type === 'success' ? 'success' : ($type === 'error' ? 'error' : ($type === 'warning' ? 'warning' : 'info')),
                                                        'size' => 'SM',
                                                        'type' => 'button',
                                                        'options' => [
                                                            'onclick' => 'showFlash' . ucfirst($type) . '()'
                                                        ]
                                                    ]) ?>
                                                    <script>
                                                        function showFlash<?= ucfirst($type) ?>() {
                                                            const container = document.getElementById('flash-container');
                                                            container.innerHTML = `<?= $this->element('atoms/flash', [
                                                                'message' => $messages[$type],
                                                                'type' => $type
                                                            ]) ?>`;
                                                        }
                                                    </script>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="mt-6 border-t border-gray-200 pt-6">
                                                <p class="text-sm font-semibold text-gray-900 mb-3">Try Dynamic Rendering:</p>
                                                <div id="dynamic-flash-container" class="min-h-[80px] mb-4"></div>
                                                <div class="flex flex-wrap gap-3">
                                                    <?php
                                                    $dynamicMessages = [
                                                        'success' => 'Dynamic success message rendered via JavaScript!',
                                                        'error' => 'Dynamic error message without page reload!',
                                                        'warning' => 'Dynamic warning message injected on the fly!',
                                                        'info' => 'Dynamic info message added dynamically!'
                                                    ];
                                                    foreach (['success', 'error', 'warning', 'info'] as $type):
                                                    ?>
                                                        <?= $this->element('atoms/button', [
                                                            'label' => ucfirst($type),
                                                            'variant' => $type,
                                                            'size' => 'SM',
                                                            'icon' => constant('\App\Utility\Icon::' . strtoupper(str_replace(['success', 'error', 'warning', 'info'], ['CHECK_CIRCLE', 'ALERT_TRIANGLE', 'ALERT_SQUARE', 'INFO_CIRCLE'], $type))),
                                                            'type' => 'button',
                                                            'options' => [
                                                                'onclick' => 'showDynamicFlash' . ucfirst($type) . '()'
                                                            ]
                                                        ]) ?>
                                                        <script>
                                                            function showDynamicFlash<?= ucfirst($type) ?>() {
                                                                const container = document.getElementById('dynamic-flash-container');
                                                                container.innerHTML = `<?= $this->element('atoms/flash', [
                                                                    'message' => $dynamicMessages[$type],
                                                                    'type' => $type
                                                                ]) ?>`;
                                                            }
                                                        </script>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($section['component'] === 'comment_card' && isset($example['params']['show_mock_comment'])): ?>
                                        <!-- Mock Comment Card Demo -->
                                        <?php
                                        // Create mock User entity
                                        $mockUser = new \stdClass();
                                        $mockUser->full_name = $example['params']['mock_comment_data']['user_full_name'];

                                        // Create mock Comment entity with required properties
                                        $mockComment = new \stdClass();
                                        $mockComment->content = $example['params']['mock_comment_data']['content'];
                                        $mockComment->reference_id = $example['params']['mock_comment_data']['reference_id'];
                                        $mockComment->created = new \Cake\I18n\DateTime($example['params']['mock_comment_data']['created']);
                                        $mockComment->user = $mockUser;

                                        // Render the comment card with mock entity
                                        echo $this->element($component['path'], [
                                            'comment' => $mockComment,
                                            'depth' => 0
                                        ]);
                                        ?>
                                    <?php elseif ($section['component'] === 'comment_thread' && isset($example['params']['show_mock_thread'])): ?>
                                        <!-- Mock Comment Thread Demo -->
                                        <?php
                                        require_once ROOT . DS . 'templates' . DS . 'element' . DS . 'demo' . DS . 'mock_comments.php';
                                        echo $this->element($component['path'], [
                                            'comments' => buildMockCommentThread($example['params']['mock_thread_data']),
                                            'depth' => 0
                                        ]);
                                        ?>
                                    <?php else: ?>
                                        <!-- Standard Component Display -->
                                        <div class="flex flex-wrap gap-4 items-center">
                                            <?php
                                            $processIconEnums = function($data) use (&$processIconEnums) {
                                                if (is_array($data)) {
                                                    foreach ($data as $key => $value) {
                                                        $data[$key] = $processIconEnums($value);
                                                    }
                                                    return $data;
                                                } elseif (is_string($data) && str_starts_with($data, 'Icon::')) {
                                                    $enumParts = explode('::', $data);
                                                    if (count($enumParts) === 2 && $enumParts[0] === 'Icon') {
                                                        try {
                                                            return constant('\App\Utility\Icon::' . $enumParts[1]);
                                                        } catch (\Error $e) {
                                                            return $data;
                                                        }
                                                    }
                                                }
                                                return $data;
                                            };

                                            $processedParams = $processIconEnums($example['params']);
                                            ?>
                                            <?= $this->element($component['path'], $processedParams) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Code Accordion -->
                                <?php
                                ob_start();
                                ?>
                                <div class="space-y-4">
                                    <!-- Usage Code -->
                                    <div>
                                        <h5 class="text-sm font-semibold text-gray-700 mb-2">Usage:</h5>
                                        <pre class="text-sm bg-gray-100 p-4 rounded border overflow-x-auto"><code>&lt;?= $this-&gt;element('<?= h($component['path']) ?>', <?= var_export($example['params'], true) ?>) ?&gt;</code></pre>
                                    </div>

                                    <!-- Parameters Used -->
                                    <div>
                                        <h5 class="text-sm font-semibold text-gray-700 mb-2">Parameters:</h5>
                                        <div class="bg-gray-100 p-4 rounded border">
                                            <pre class="text-sm"><code><?= json_encode($example['params'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></code></pre>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $codeContent = ob_get_clean();
                                ?>

                                <div class="border-t border-gray-200">
                                    <?= $this->element('atoms/accordion_item', [
                                        'id' => $section['id'] . '-example-' . $index,
                                        'title' => 'View Code & Implementation',
                                        'content' => $codeContent,
                                        'open' => false,
                                        'escape' => false,
                                        'options' => ['class' => 'border-0 rounded-none']
                                    ]) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Fallback for components without defined examples -->
                    <div class="p-6 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div class="flex items-center">
                            <?= $this->element('atoms/icon', [
                                'name' => 'info-circle',
                                'size' => 'sm',
                                'options' => ['class' => 'text-yellow-600 mr-3']
                            ]) ?>
                            <div>
                                <p class="text-yellow-800 font-medium">No examples available</p>
                                <p class="text-yellow-700 text-sm">
                                    <?php if ($component['status'] === 'pending'): ?>
                                        This component is still under development.
                                    <?php else: ?>
                                        Examples for this component haven't been defined yet.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

        <?php endforeach; ?>

    </div> <!-- end component sections -->

<?php
$componentSections = ob_get_clean();

echo $this->element('molecules/card', [
    'title' => $meta['title'] ?? 'UI Component Demo',
    'subtitle' => $meta['description'] ?? 'Live examples of molecules and organisms with different variants and configurations.',
    'body' => $componentSections,
    'escape' => false
]);
?>
