<?php
// Prepare notifications data for Alpine.js
$notificationsData = json_encode(array_values(array_map(function($n) {
    return [
        'id' => $n->id,
        'user_name' => $n->user->full_name ?? 'Unknown User',
        'user_initials' => $this->Layout->getInitials($n->user->full_name ?? 'UN'),
        'created' => $n->created->toIso8601String(),
        'created_display' => $n->created->format('d.m.Y, H:i \U\h\r'),
        'process_title' => $n->process->title ?? 'Unknown Process',
        'type' => $n->type ?? '',
        'description' => $n->description,
        'seen' => $n->seen
    ];
}, $notifications)));

// Define columns for the table
$notificationColumns = [
    [
        'field' => 'created_display',
        'label' => __('Date'),
        'sortable' => true,
        'sortField' => 'created',
        'width' => '15%'
    ],
    [
        'field' => 'description',
        'label' => __('Message'),
        'sortable' => false,
        'width' => '55%'
    ],
    [
        'field' => 'type',
        'label' => __('Type'),
        'sortable' => true,
        'width' => '20%'
    ]
];
?>
<?php if (!empty($notifications)) : ?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationsTable', () => ({
        notifications: <?= $notificationsData ?>,
        dismissedIds: new Set(),
        sortField: null,
        sortDirection: 'asc',
        get visibleNotifications() {
            return this.notifications.filter(n =>
                !n.seen || this.dismissedIds.has(n.id)
            );
        },
        get unseenCount() {
            return this.notifications.filter(n =>
                !n.seen && !this.dismissedIds.has(n.id)
            ).length;
        },
        get sortedData() {
            let filtered = this.visibleNotifications;
            if (!this.sortField) return filtered;

            return [...filtered].sort((a, b) => {
                let aVal = a[this.sortField];
                let bVal = b[this.sortField];

                if (this.sortField === 'created') {
                    aVal = new Date(aVal).getTime();
                    bVal = new Date(bVal).getTime();
                }

                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        dismissNotification(notificationId) {
            this.dismissedIds.add(notificationId);

            const url = '<?= $this->Url->build(['controller' => 'Notifications', 'action' => 'markAsSeen', '__ID__']) ?>'.replace('__ID__', notificationId);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(err => {
                console.error('Failed to mark notification as seen:', err);
            });
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

<div class="bg-white rounded-lg shadow-sm overflow-hidden w-full" x-data="notificationsTable">
    <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-200">
        <?= $this->element('atoms/icon', [
            'name' => 'bell',
            'size' => 'md',
            'options' => ['class' => 'text-brand-deep']
        ]) ?>
        <h3 class="text-xl font-semibold text-brand-deep"><?= __('Notifications') ?></h3>
        <?php if (count($notifications) > 0): ?>
            <span x-show="unseenCount > 0"
                  x-text="unseenCount"
                  class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold text-white bg-warning-500 rounded-full"></span>
        <?php endif; ?>
    </div>

    <div x-show="visibleNotifications.length === 0" class="flex flex-col items-center justify-center py-12 px-6 text-center">
        <?= $this->element('atoms/icon', [
            'name' => 'bell',
            'size' => 'xl',
            'options' => ['class' => 'text-gray-400 mb-4']
        ]) ?>
        <h4 class="text-lg font-semibold text-gray-900 mb-2"><?= __('No new notifications') ?></h4>
        <p class="text-sm text-gray-500"><?= __('You are all caught up! Check back later for updates.') ?></p>
    </div>

    <!-- Desktop Table View -->
    <div x-show="visibleNotifications.length > 0" class="hidden md:block overflow-x-auto">
        <table class="w-full [table-layout:fixed]">
            <thead class="bg-brand-lightest">
                <tr class="border-b border-gray-200">
                    <?php foreach ($notificationColumns as $column): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider" width="<?= $column['width'] ?? 'auto' ?>">
                            <?php if ($column['sortable'] ?? false): ?>
                                <button @click="sort('<?= h($column['sortField'] ?? $column['field']) ?>')" class="flex items-center gap-1 hover:text-gray-700 transition-colors">
                                    <?= h($column['label']) ?>
                                    <?= $this->element('atoms/icon', [
                                        'name' => 'chevron-selector',
                                        'size' => 'xs',
                                        'options' => [
                                            'class' => 'transition-opacity',
                                            'x-bind:class' => "sortField === '" . h($column['sortField'] ?? $column['field']) . "' ? 'opacity-100' : 'opacity-30'"
                                        ]
                                    ]) ?>
                                </button>
                            <?php else: ?>
                                <?= h($column['label']) ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 tracking-wider" width="10%"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="notification in sortedData" :key="notification.id">
                    <tr class="hover:bg-gray-50 transition-colors"
                        :class="!notification.seen && !dismissedIds.has(notification.id) ? 'font-semibold text-primary' : 'text-gray-900'">
                        <td class="px-6 py-4">
                            <time class="text-sm" :datetime="notification.created" x-text="notification.created_display"></time>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm" x-text="notification.description"></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm" x-text="notification.type"></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <template x-if="!notification.seen && !dismissedIds.has(notification.id)">
                                <?= $this->element('atoms/button', [
                                    'icon' => 'x-close',
                                    'variant' => 'ghost',
                                    'size' => 'XS',
                                    'click' => 'dismissNotification(notification.id)',
                                    'options' => [
                                        'title' => __('Close'),
                                        'x-bind:data-notification-id' => 'notification.id'
                                    ]
                                ]) ?>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div x-show="visibleNotifications.length > 0" class="md:hidden divide-y divide-gray-200">
        <template x-for="notification in sortedData" :key="notification.id">
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="space-y-3">
                    <div class="flex justify-end -mb-4 border-t border-gray-100">
                        <template x-if="!notification.seen && !dismissedIds.has(notification.id)">
                            <?= $this->element('atoms/button', [
                                'icon' => 'x-close',
                                'variant' => 'ghost',
                                'size' => 'SM',
                                'click' => 'dismissNotification(notification.id)',
                                'options' => [
                                    'x-bind:data-notification-id' => 'notification.id'
                                ]
                            ]) ?>
                        </template>
                    </div>
                    <div x-show="notification.created_display">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            <?= __('Datum') ?>
                        </dt>
                        <dd class="text-sm"
                            :class="!notification.seen && !dismissedIds.has(notification.id) ? 'font-semibold text-primary' : 'text-gray-900'"
                            x-text="notification.created_display"></dd>
                    </div>
                    <div x-show="notification.description">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            <?= __('Nachricht') ?>
                        </dt>
                        <dd class="text-sm"
                            :class="!notification.seen && !dismissedIds.has(notification.id) ? 'font-semibold text-primary' : 'text-gray-900'"
                            x-text="notification.description"></dd>
                    </div>
                    <div x-show="notification.type">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                            <?= __('Typ') ?>
                        </dt>
                        <dd class="text-sm"
                            :class="!notification.seen && !dismissedIds.has(notification.id) ? 'font-semibold text-primary' : 'text-gray-900'"
                            x-text="notification.type"></dd>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
<?php endif; ?>
