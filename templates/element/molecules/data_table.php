<?php
/**
 * Data Table Molecule Component
 *
 * Responsive table component with sortable headers and action columns.
 * Used for displaying and managing data in admin interfaces.
 *
 * @var \App\View\AppView $this
 * @var array $headers Table headers configuration (required)
 * @var array $rows Table data rows (required)
 * @var array $actions Common action buttons configuration (optional)
 * @var bool $sortable Whether table supports sorting (default: true)
 * @var bool $responsive Whether table should be responsive (default: true)
 * @var array $options Additional HTML attributes for the table
 * @var bool $escape Whether to escape cell content (default: true)
 */

// Set defaults
$headers = $headers ?? [];
$rows = $rows ?? [];
$actions = $actions ?? [];
$sortable = $sortable ?? true;
$responsive = $responsive ?? true;
$options = $options ?? [];
$escape = $escape ?? true;

// Validate required fields
if (empty($headers) || empty($rows)) {
    return; // Don't render table without headers and data
}

// Build container classes
$containerClasses = [];
if ($responsive) {
    $containerClasses[] = 'table-responsive';
}

// Build table classes
$tableClasses = ['table'];

// Add user-provided classes
if (isset($options['class'])) {
    $tableClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $tableClasses);

// Helper function to render cell content
function renderCell($content, $escape) {
    if (is_array($content)) {
        return $content['html'] ?? ($escape ? h($content['text'] ?? '') : ($content['text'] ?? ''));
    }
    return $escape ? h($content) : $content;
}
?>

<?php if ($responsive): ?>
<div class="<?= implode(' ', $containerClasses) ?>">
<?php endif; ?>

    <table<?= $this->Html->templater()->formatAttributes($options) ?>>
        <thead>
            <tr>
                <?php foreach ($headers as $key => $header): ?>
                    <th<?= isset($header['class']) ? ' class="' . h($header['class']) . '"' : '' ?>>
                        <?php if ($sortable && isset($header['sort']) && $header['sort']): ?>
                            <?= $this->Paginator->sort($key, $header['label'] ?? $key) ?>
                        <?php else: ?>
                            <?= $escape ? h($header['label'] ?? $key) : ($header['label'] ?? $key) ?>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>

                <?php if (!empty($actions)): ?>
                    <th class="actions"><?= __('Actions') ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($headers as $key => $header): ?>
                        <td>
                            <?= renderCell($row[$key] ?? '', $escape) ?>
                        </td>
                    <?php endforeach; ?>

                    <?php if (!empty($actions)): ?>
                        <td class="actions">
                            <?php foreach ($actions as $action): ?>
                                <?php if (isset($action['type']) && $action['type'] === 'postLink'): ?>
                                    <?= $this->Form->postLink(
                                        $action['label'],
                                        array_merge($action['url'] ?? [], ['id' => $row['id'] ?? null]),
                                        array_merge($action['options'] ?? [], [
                                            'method' => $action['method'] ?? 'delete',
                                            'confirm' => $action['confirm'] ?? null
                                        ])
                                    ) ?>
                                <?php else: ?>
                                    <?= $this->Html->link(
                                        $action['label'],
                                        array_merge($action['url'] ?? [], ['id' => $row['id'] ?? null]),
                                        $action['options'] ?? []
                                    ) ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php if ($responsive): ?>
</div>
<?php endif; ?>