<?php
/**
 * Tabs Molecule
 *
 * Horizontal tabs component using Alpine.js for interactivity.
 *
 * @var \App\View\AppView $this
 * @var array $tabs Array of tab items with structure:
 *   [
 *     ['id' => 'tab1', 'label' => 'Tab 1', 'content' => 'Content...'],
 *     ['id' => 'tab2', 'label' => 'Tab 2', 'content' => 'Content...'],
 *   ]
 * @var string|null $defaultTab ID of the tab to show by default (defaults to first tab)
 * @var bool $escape Whether to escape content (default: true)
 * @var array $options Additional HTML attributes for the container
 */

$tabs = $tabs ?? [];
$escape = $escape ?? true;
$options = $options ?? [];

if (empty($tabs)) {
    return;
}

$defaultTab = $defaultTab ?? ($tabs[0]['id'] ?? 'tab-0');
$containerId = $options['id'] ?? uniqid('tabs-');

$containerClass = 'mki-tabs ' . ($options['class'] ?? '');
?>
<div
    id="<?= h($containerId) ?>"
    class="<?= h(trim($containerClass)) ?>"
    x-data="{ activeTab: '<?= h($defaultTab) ?>' }"
>
    <div class="mki-tabs-list flex border-b border-gray-200" role="tablist">
        <?php foreach ($tabs as $index => $tab):
            $tabId = $tab['id'] ?? 'tab-' . $index;
        ?>
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === '<?= h($tabId) ?>'"
                aria-controls="<?= h($containerId) ?>-panel-<?= h($tabId) ?>"
                @click="activeTab = '<?= h($tabId) ?>'"
                class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors"
                :class="activeTab === '<?= h($tabId) ?>'
                    ? 'border-brand text-brand'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            >
                <?= h($tab['label'] ?? '') ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="mki-tabs-panels mt-4">
        <?php foreach ($tabs as $index => $tab):
            $tabId = $tab['id'] ?? 'tab-' . $index;
            $content = $tab['content'] ?? '';
        ?>
            <div
                id="<?= h($containerId) ?>-panel-<?= h($tabId) ?>"
                role="tabpanel"
                x-show="activeTab === '<?= h($tabId) ?>'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            >
                <?= $escape ? h($content) : $content ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
