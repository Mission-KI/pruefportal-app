<?php
/**
 * Workflow Navigation Molecule
 *
 * Unified navigation for multi-step workflows (VCIO, PNA).
 * Displays quality dimensions with status indicators.
 *
 * @var \App\View\AppView $this
 * @var string $title Navigation title (e.g., "VCIO-Einstufung")
 * @var array $overview_url CakePHP URL array for overview link
 * @var array $items Navigation items with structure:
 *   [
 *     'title' => string,       // Display title
 *     'key' => string,         // Suffix like "DA", "TR"
 *     'status' => string,      // 'upcoming', 'current', 'completed'
 *     'url' => array|null,     // Link URL
 *   ]
 * @var string|null $current_label Label for current item (default: "Currently editing")
 */

$title = $title ?? '';
$overview_url = $overview_url ?? [];
$items = $items ?? [];
$current_label = $current_label ?? __('Currently editing');
?>

<nav class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <?php if (!empty($title)): ?>
        <h3 class="text-md font-semibold text-brand mb-4"><?= h($title) ?></h3>
    <?php endif; ?>

    <?= $this->Html->link(
        '← ' . __('Übersicht'),
        $overview_url,
        ['class' => 'block text-sm font-medium text-brand hover:bg-gray-50 rounded-lg mb-3 underline']
    ) ?>

    <ol class="space-y-2">
        <?php foreach ($items as $item): ?>
            <?php
            $status = $item['status'] ?? 'upcoming';
            $itemTitle = $item['title'] ?? '';
            $key = $item['key'] ?? null;
            $url = $item['url'] ?? null;
            $displayTitle = h($itemTitle) . ($key ? ' (' . h($key) . ')' : '');
            ?>

            <li>
                <?php if ($status === 'completed'): ?>
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50">
                        <?= $this->Html->link(
                            $displayTitle,
                            $url,
                            ['class' => 'text-sm font-medium text-brand underline']
                        ) ?>
                        <?= $this->element('atoms/icon', [
                            'name' => 'check-circle',
                            'size' => 'sm',
                            'options' => ['class' => 'text-green-500 ml-2']
                        ]) ?>
                    </div>

                <?php elseif ($status === 'current'): ?>
                    <div class="bg-blue-50 rounded-lg px-3 py-2">
                        <span class="text-sm font-medium text-brand-deep"><?= $displayTitle ?></span>
                        <?php if ($current_label): ?>
                            <span class="block mt-1 text-xs text-gray-500"><?= h($current_label) ?></span>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="px-3 py-2">
                        <span class="text-sm text-gray-400"><?= $displayTitle ?></span>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
