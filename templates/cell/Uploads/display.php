<?php
/**
 * @var App\View\Cell\ProcessesCell $processes
 * @var App\View\Cell\ProcessesCell $uploads
 * @var App\View\Helper\LayoutHelper $Layout
 * @var int|null $process_id Currently selected process ID
 */
?>

<?php
$columns = [
    [
        'label' => __('Dateiname'),
        'field' => 'name',
        'width' => '40%',
        'renderer' => function($row, $view) {
            $icon = \App\Utility\FileIcon::fromFilename($row->name);
            $iconHtml = $view->element('atoms/icon', [
                'name' => $icon,
                'size' => 'md'
            ]);
            return '<div class="flex items-center gap-3">' . $iconHtml . ' <span class="truncate">' . h($row->name) . '</span></div>';
        }
    ],
    [
        'label' => __('Datum'),
        'field' => 'created',
        'width' => '25%',
        'nowrap' => true,
        'renderer' => function($row, $view) {
            return $view->element('atoms/timestamp', [
                'datetime' => $row->created,
                'format' => 'd.m.y - H:i \U\h\r'
            ]);
        }
    ],
    [
        'label' => __('Dateigröße'),
        'field' => 'size',
        'width' => '20%',
        'align' => 'right',
        'nowrap' => true,
        'renderer' => function($row, $view) {
            $size = $view->Layout->humanFilesize($row->size);
            return '<span class="text-sm text-gray-500">' . h($size) . '</span>';
        }
    ]
];

$actionRenderer = function($row, $view) {
    return $view->element('molecules/table_actions', [
        'actions' => [
            [
                'icon' => 'download-01',
                'url' => ['controller' => 'Uploads', 'action' => 'ajaxView', urlencode($row->key)],
                'title' => __('Download'),
                'class' => 'js-load-upload'
            ],
// TODO Who can delete an Upload?
//            [
//                'icon' => 'trash-01',
//                'url' => ['controller' => 'Uploads', 'action' => 'delete', $row->id],
//                'title' => __('Delete'),
//                'method' => 'post',
//                'confirm' => __('Are you sure you want to delete {0}?', $row->name)
//            ]
        ]
    ]);
};
?>

<?php if (!empty($processes) || !empty($uploads)): ?>
    <?php
    // Prepare footer content
    ob_start();
    ?>
    <div class="flex flex-wrap gap-3 items-center justify-end">
        <?= $this->element('atoms/button', [
            'label' => __('Dokument hinzufügen'),
            'icon' => 'plus',
            'variant' => 'primary',
            'size' => 'SM',
            'click' => 'handleUpload()',
            'options' => ['class' => 'whitespace-nowrap']
        ]) ?>
        <?php /* Post MVP
        <div x-show="selectedRows.size > 0" x-cloak>
            <?= $this->element('atoms/button', [
                'label' => __('Auswahl herunterladen'),
                'icon' => 'download-01',
                'variant' => 'secondary',
                'size' => 'SM',
                'click' => 'handleDownloadSelected()',
                'options' => ['class' => 'whitespace-nowrap']
            ]) ?>
        </div>
        */ ?>
    </div>
    <?php
    $footerContent = ob_get_clean();

    // Prepare main content
    ob_start();
    ?>

        <!-- Hidden form to get CSRF token -->
        <?= $this->Form->create(null, [
            'url' => ['controller' => 'Uploads', 'action' => 'upload'],
            'type' => 'file',
            'id' => 'upload-form',
            'style' => 'display: none;'
        ]) ?>
            <?= $this->Form->control('process_id', ['type' => 'hidden', 'value' => $process_id]) ?>
        <?= $this->Form->end() ?>

        <!-- Upload Queue and Table -->
        <div x-data="uploadsHandler(<?= $process_id ?? 'null' ?>)"
             @upload-document.window="handleUpload($event.detail)">

            <!-- Upload Queue -->
            <div x-show="uploadQueue.length > 0" class="mb-6 space-y-3">
                <template x-for="(item, index) in uploadQueue" :key="item.id">
                    <div class="bg-white rounded-lg p-4 border transition-colors"
                         :class="{
                             'border-red-600': item.status === 'error',
                             'border-gray-200': item.status !== 'error'
                         }">
                        <div class="flex items-start gap-3">
                            <!-- File Icon - dynamically rendered based on file type -->
                            <div class="flex-shrink-0" x-html="item.iconHtml">
                            </div>

                            <!-- File Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="item.name"></p>

                                <!-- Status Line -->
                                <div class="flex items-center gap-2 mt-1 text-xs">
                                    <span class="text-gray-500" x-text="item.progressText"></span>
                                    <span class="text-gray-500">|</span>
                                    <span x-show="item.status === 'uploading'" class="flex items-center gap-1 text-blue-600">
                                        <?= $this->element('atoms/icon', ['name' => 'refresh', 'size' => 'xs']) ?>
                                        <?= __('Lädt hoch...') ?>
                                    </span>
                                    <span x-show="item.status === 'complete'" class="flex items-center gap-1 text-green-600">
                                        <?= $this->element('atoms/icon', ['name' => 'check-circle', 'size' => 'xs']) ?>
                                        <?= __('Upload vollständig') ?>
                                    </span>
                                    <span x-show="item.status === 'error'" class="flex items-center gap-1 text-red-600">
                                        <?= $this->element('atoms/icon', ['name' => 'x-circle', 'size' => 'xs']) ?>
                                        <?= __('Upload gescheitert') ?>
                                    </span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-2 flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-light-web rounded-full transition-all duration-300"
                                             :style="`width: ${item.progress}%`"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 w-10 text-right" x-text="`${Math.round(item.progress)}%`"></span>
                                </div>

                                <!-- Error Message -->
                                <p x-show="item.status === 'error'" class="mt-2 text-sm text-red-600">
                                    <?= __('Bitte erneut versuchen') ?>
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <button @click="removeFromQueue(index)" type="button" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                                <?= $this->element('atoms/icon', [
                                    'name' => 'trash-01',
                                    'size' => 'sm'
                                ]) ?>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Uploads Table -->
            <?= $this->element('organisms/sortable_table', [
                'data' => $uploads,
                'columns' => $columns,
                'features' => [
                    'sortable' => false,
                    'selectable' => false,
                    'actions' => true
                ],
                'emptyState' => [
                    'icon' => 'file-save',
                    'title' => __('Keine Dokumente'),
                    'message' => __('Keine Dokumente für diesen Prozess gefunden')
                ],
                'actionRenderer' => $actionRenderer
            ]) ?>
        </div>

        <script>
        // Pre-render file icons server-side for client use
        const fileIcons = {
            <?php
            $iconTypes = [
                'pdf' => 'file-icons/PDF, Type=Default',
                'doc' => 'file-icons/DOC, Type=Default',
                'docx' => 'file-icons/DOCX, Type=Default',
                'xls' => 'file-icons/XLS, Type=Default',
                'xlsx' => 'file-icons/XLSX, Type=Default',
                'ppt' => 'file-icons/PPT, Type=Default',
                'pptx' => 'file-icons/PPTX, Type=Default',
                'jpg' => 'file-icons/JPG, Type=Default',
                'jpeg' => 'file-icons/JPG, Type=Default',
                'png' => 'file-icons/PNG, Type=Default',
                'gif' => 'file-icons/Image, Type=Default',
                'csv' => 'file-icons/CSV, Type=Default',
                'default' => 'file-icons/Document, Type=Default'
            ];

            $iconHtmls = [];
            foreach ($iconTypes as $ext => $iconName) {
                $iconHtml = $this->element('atoms/icon', [
                    'name' => $iconName,
                    'size' => 'xl',
                    'options' => ['class' => 'text-gray-500']
                ]);
                $iconHtmls[] = "'{$ext}': " . json_encode($iconHtml);
            }
            echo implode(",\n            ", $iconHtmls);
            ?>
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('uploadsHandler', (processId) => ({
                processId: processId,
                uploadQueue: [],
                nextId: 0,

                handleUpload(detail) {
                    // Create file input to trigger file picker
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.multiple = true;
                    input.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xlsx,.xls,.csv,.ppt,.pptx';

                    input.onchange = (e) => {
                        const files = Array.from(e.target.files);
                        if (files.length === 0) return;

                        // Get CSRF token from hidden form
                        const csrfInput = document.querySelector('#upload-form input[name="_csrfToken"]');
                        const csrfToken = csrfInput ? csrfInput.value : null;

                        if (!csrfToken) {
                            console.error('CSRF token not found');
                            alert('Security token not found. Please refresh the page.');
                            return;
                        }

                        // Upload each file
                        files.forEach(file => this.uploadFile(file, csrfToken));
                    };

                    input.click();
                },

                uploadFile(file, csrfToken) {
                    const queueItem = {
                        id: this.nextId++,
                        name: file.name,
                        size: file.size,
                        progress: 0,
                        progressText: this.formatProgress(0, file.size),
                        status: 'uploading',
                        iconHtml: this.getFileIconHtml(file.name)
                    };

                    this.uploadQueue.push(queueItem);
                    const queueIndex = this.uploadQueue.length - 1;

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('process_id', this.processId);

                    const xhr = new XMLHttpRequest();

                    // Track upload progress
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            this.uploadQueue[queueIndex].progress = percentComplete;
                            this.uploadQueue[queueIndex].progressText = this.formatProgress(e.loaded, e.total);
                        }
                    });

                    // Handle completion
                    xhr.addEventListener('load', () => {
                        if (xhr.status === 200 || xhr.status === 302) {
                            this.uploadQueue[queueIndex].status = 'complete';
                            this.uploadQueue[queueIndex].progress = 100;
                            this.uploadQueue[queueIndex].progressText = this.formatProgress(file.size, file.size);

                            // Remove from queue after 2 seconds and reload to show in table
                            setTimeout(() => {
                                this.uploadQueue.splice(queueIndex, 1);
                                if (this.uploadQueue.length === 0) {
                                    window.location.reload();
                                }
                            }, 2000);
                        } else {
                            // Error response from server
                            this.uploadQueue[queueIndex].status = 'error';
                            this.uploadQueue[queueIndex].progress = 0;
                            this.uploadQueue[queueIndex].progressText = this.formatProgress(0, file.size);
                        }
                    });

                    // Handle network errors
                    xhr.addEventListener('error', () => {
                        this.uploadQueue[queueIndex].status = 'error';
                        this.uploadQueue[queueIndex].progress = 0;
                        this.uploadQueue[queueIndex].progressText = this.formatProgress(0, file.size);
                    });

                    xhr.open('POST', '/uploads/upload');
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.send(formData);
                },

                formatProgress(loaded, total) {
                    const formatBytes = (bytes) => {
                        if (bytes < 1024) return bytes + ' B';
                        if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' KB';
                        return (bytes / 1048576).toFixed(1) + ' MB';
                    };
                    return `${formatBytes(loaded)} of ${formatBytes(total)}`;
                },

                getFileIconHtml(filename) {
                    const ext = filename.split('.').pop().toLowerCase();
                    return fileIcons[ext] || fileIcons['default'];
                },

                removeFromQueue(index) {
                    this.uploadQueue.splice(index, 1);
                }
            }));
        });
        </script>

    <?php
    $widgetContent = ob_get_clean();

    // Render dashboard widget
    echo $this->element('organisms/dashboard_widget', [
        'icon' => 'file-save',
        'title' => __('Dokumente'),
        'processes' => !empty($processes) ? $processes : null,
        'process_id' => $process_id,
        'filter_redirect' => 'participants',
        'content' => $widgetContent,
        'footer' => $footerContent
    ]);
    ?>
<?php endif; ?>
