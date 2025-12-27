<?php
/**
 * Sortable Table Organism
 *
 * Full table with Alpine.js sorting and selection logic.
 * Provides a complete, interactive table experience with composable configuration.
 *
 * @var \App\View\AppView $this
 * @var array $data Table data rows (required)
 * @var array $columns Column definitions (required)
 * @var array $features Enabled features (optional)
 *   ['sortable' => bool, 'selectable' => bool, 'actions' => bool, 'responsive' => bool]
 * @var array $emptyState Empty state configuration (optional)
 *   ['icon' => string, 'title' => string, 'message' => string]
 * @var array $header Optional header configuration (optional)
 *   ['icon' => string, 'title' => string, 'badge' => bool, 'badgeContent' => string]
 * @var callable|null $actionRenderer Custom action rendering function (optional)
 * @var string|null $alpineName Alpine.js component name (default: 'sortableTable')
 * @var bool $externalAlpine Use external Alpine component (default: false)
 * @var array $options Additional HTML attributes for table wrapper
 */

$data = $data ?? [];
$columns = $columns ?? [];
$features = $features ?? [];
$emptyState = $emptyState ?? [];
$header = $header ?? null;
$actionRenderer = $actionRenderer ?? null;
$alpineName = $alpineName ?? 'sortableTable';
$externalAlpine = $externalAlpine ?? false;
$options = $options ?? [];
$footerContent = $footerContent ?? null;


if (!is_array($data)) {
    if (\Cake\Core\Configure::read('debug')) {
        trigger_error('sortable_table: $data must be an array, ' . gettype($data) . ' given', E_USER_WARNING);
    }
    $data = [];
}

if (empty($columns)) {
    return;
}

$sortable = $features['sortable'] ?? true;
$selectable = $features['selectable'] ?? false;
$hasActions = $features['actions'] ?? false;
$responsive = $features['responsive'] ?? true;

$dataJson = json_encode(array_values(array_map(function($row) use ($columns) {
    $mapped = [];

    if (is_object($row) && isset($row->id)) {
        $mapped['id'] = $row->id;
    } elseif (is_array($row) && isset($row['id'])) {
        $mapped['id'] = $row['id'];
    }

    foreach ($columns as $column) {
        $field = $column['field'] ?? '';
        if ($field) {
            if (is_object($row)) {
                $mapped[$field] = $row->{$field} ?? null;
            } elseif (is_array($row)) {
                $mapped[$field] = $row[$field] ?? null;
            }
        }
    }

    return $mapped;
}, $data)));

$defaultEmptyState = [
    'icon' => 'file-save',
    'title' => __('No data'),
    'message' => __('No data available to display'),
];
$emptyState = array_merge($defaultEmptyState, $emptyState);
?>

<?php if ($sortable && !$externalAlpine): ?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('<?= h($alpineName) ?>', () => ({
        data: <?= $dataJson ?>,
        selectedRows: new Set(),
        sortField: null,
        sortDirection: 'asc',

        get totalRows() {
            return this.data.length;
        },

        get allSelected() {
            return this.selectedRows.size > 0 && this.selectedRows.size === this.totalRows;
        },

        get sortedData() {
            let sorted = [...this.data];

            if (!this.sortField) return sorted;

            return sorted.sort((a, b) => {
                let aVal = a[this.sortField];
                let bVal = b[this.sortField];

                if (aVal === null || aVal === undefined) return 1;
                if (bVal === null || bVal === undefined) return -1;

                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal?.toLowerCase() || '';
                }

                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },

        toggleAll() {
            if (this.allSelected) {
                this.selectedRows.clear();
            } else {
                this.data.forEach(row => this.selectedRows.add(row.id));
            }
        },

        toggleRow(id) {
            if (this.selectedRows.has(id)) {
                this.selectedRows.delete(id);
            } else {
                this.selectedRows.add(id);
            }
        },

        sort(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
        }
    }));
});
</script>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm overflow-hidden w-full" <?= ($sortable || $externalAlpine) ? 'x-data="' . h($alpineName) . '"' : '' ?>>
    <?php if ($header): ?>
        <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-200">
            <?php if (isset($header['icon'])): ?>
                <?= $this->element('atoms/icon', [
                    'name' => $header['icon'],
                    'size' => 'md',
                    'options' => ['class' => 'text-brand-deep']
                ]) ?>
            <?php endif; ?>
            <h3 class="text-xl font-semibold text-brand-deep"><?= h($header['title'] ?? '') ?></h3>
            <?php if (isset($header['badge']) && $header['badge']): ?>
                <span <?= isset($header['badgeContent']) ? '' : 'x-show="unseenCount > 0"' ?>
                      <?= isset($header['badgeContent']) ? '' : 'x-text="unseenCount"' ?>
                      class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold text-white bg-warning-500 rounded-full">
                    <?= isset($header['badgeContent']) ? h($header['badgeContent']) : '' ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($data)): ?>
        <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
            <?= $this->element('atoms/icon', [
                'name' => $emptyState['icon'],
                'size' => 'xl',
                'options' => ['class' => 'text-gray-400 mb-4']
            ]) ?>
            <h4 class="text-lg font-semibold text-gray-900 mb-2"><?= h($emptyState['title']) ?></h4>
            <p class="text-sm text-gray-500"><?= h($emptyState['message']) ?></p>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="<?= $responsive ? 'hidden md:block' : '' ?> overflow-x-auto">
            <table class="w-full [table-layout:fixed]">
                <?= $this->element('molecules/table_header', [
                    'columns' => $columns,
                    'hasCheckbox' => $selectable,
                    'hasActions' => $hasActions
                ]) ?>

                <tbody class="divide-y divide-gray-200">
                    <?php if ($sortable || $externalAlpine): ?>
                        <template x-for="row in sortedData" :key="row.id">
                            <tr class="hover:bg-gray-50 transition-colors"
                                <?= $selectable ? 'x-bind:class="selectedRows.has(row.id) ? \'bg-brand-50\' : \'\'"' : '' ?>>

                                <?php if ($selectable): ?>
                                    <td class="px-4 py-4 w-10 text-center">
                                        <input type="checkbox"
                                               class="w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 focus:ring-2"
                                               @click="toggleRow(row.id)"
                                               x-bind:checked="selectedRows.has(row.id)">
                                    </td>
                                <?php endif; ?>

                                <?php foreach ($columns as $column): ?>
                                    <?php
                                    $field = $column['field'] ?? '';
                                    $align = $column['align'] ?? 'left';
                                    $nowrap = $column['nowrap'] ?? false;
                                    $truncate = $column['truncate'] ?? false;

                                    $cellClasses = 'px-6 py-4 text-sm text-gray-900';
                                    $cellClasses .= ' text-' . $align;
                                    if ($nowrap) $cellClasses .= ' whitespace-nowrap';
                                    if ($truncate) $cellClasses .= ' truncate';
                                    ?>
                                    <td class="<?= h($cellClasses) ?>" x-text="row.<?= h($field) ?>"></td>
                                <?php endforeach; ?>

                                <?php if ($hasActions): ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <!-- Actions rendered via Alpine template -->
                                    </td>
                                <?php endif; ?>
                            </tr>
                        </template>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <?php
                            $rowId = is_object($row) ? ($row->id ?? null) : ($row['id'] ?? null);
                            $rowActions = null;

                            if ($hasActions && $actionRenderer && is_callable($actionRenderer)) {
                                $rowActions = $actionRenderer($row, $this);
                            }
                            ?>
                            <?= $this->element('molecules/table_row', [
                                'row' => $row,
                                'columns' => $columns,
                                'hasCheckbox' => $selectable,
                                'actions' => $rowActions,
                                'rowId' => $rowId
                            ]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <?php if ($responsive): ?>
            <div class="md:hidden divide-y divide-gray-200">
                <?php if ($sortable || $externalAlpine): ?>
                    <template x-for="row in sortedData" :key="row.id">
                        <div class="p-4 hover:bg-gray-50 transition-colors"
                             <?= $selectable ? 'x-bind:class="selectedRows.has(row.id) ? \'bg-brand-50\' : \'\'"' : '' ?>>
                            <div class="space-y-3">
                                <?php foreach ($columns as $column): ?>
                                    <?php $field = $column['field'] ?? ''; ?>
                                    <?php if ($field): ?>
                                        <div x-show="row.<?= h($field) ?>">
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                                <?= h($column['label'] ?? '') ?>
                                            </dt>
                                            <dd class="text-sm text-gray-900" x-text="row.<?= h($field) ?>"></dd>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if ($hasActions): ?>
                                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                        <!-- Actions rendered via Alpine template -->
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </template>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <?php
                        $rowId = is_object($row) ? ($row->id ?? null) : ($row['id'] ?? null);
                        $rowActions = null;

                        if ($hasActions && $actionRenderer && is_callable($actionRenderer)) {
                            $rowActions = $actionRenderer($row, $this);
                        }
                        ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="space-y-3">
                                <?php foreach ($columns as $column): ?>
                                    <?php
                                    $field = $column['field'] ?? '';
                                    $cellContent = '';

                                    if (isset($column['renderer']) && is_callable($column['renderer'])) {
                                        $cellContent = $column['renderer']($row, $this);
                                    } elseif ($field) {
                                        if (is_array($row)) {
                                            $cellContent = h($row[$field] ?? '');
                                        } elseif (is_object($row)) {
                                            $cellContent = h($row->{$field} ?? '');
                                        }
                                    }
                                    ?>
                                    <?php if ($cellContent !== ''): ?>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                                                <?= h($column['label'] ?? '') ?>
                                            </dt>
                                            <dd class="text-sm text-gray-900"><?= $cellContent ?></dd>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if ($rowActions): ?>
                                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                        <?= $rowActions ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($footerContent): ?>
        <div class="p-4 hover:bg-gray-50 transition-colors">
            <div class="space-y-3">
                <?= $footerContent ?>
            </div>
        </div>
    <?php endif; ?>
</div>
