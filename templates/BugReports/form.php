<?php
/**
 * Bug Report Form Page
 *
 * @var \App\View\AppView $this
 * @var string $appVersion
 * @var string $currentUrl
 */

// Check if this is an AJAX request (modal context)
$isAjax = $this->request->is('ajax');

if (!$isAjax) {
    $this->assign('title', __('Fehler melden'));
}

// Prepare default description
$defaultDescription = "## System-Information
- **App-Version:** {$appVersion}
- **URL:** {$currentUrl}
- **Browser:** (wird automatisch ausgefüllt)
- **Datum:** " . date('d.m.Y H:i') . "

## Beschreibung
[Hier Fehlerbeschreibung einfügen]

## Schritte zur Reproduktion
1.
2.
3.

## Erwartetes Verhalten


## Tatsächliches Verhalten

";
?>

<?php if (!$isAjax): ?>
<div class="max-w-4xl mx-auto" x-data="bugReportForm(false)">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h1 class="text-xl font-semibold text-gray-900">
                <?= __('Fehler melden') ?>
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                <?= __('Helfen Sie uns, das Prüfportal zu verbessern, indem Sie Fehler melden.') ?>
            </p>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
<?php else: ?>
<div x-data="bugReportForm(true)">
    <p class="text-sm text-gray-600 mb-4">
        <?= __('Helfen Sie uns, das Prüfportal zu verbessern, indem Sie Fehler melden.') ?>
    </p>
<?php endif; ?>
            <!-- Success State -->
            <div x-show="success" x-cloak class="text-center py-8">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h2 class="text-xl font-medium text-gray-900 mb-2"><?= __('Vielen Dank!') ?></h2>
                <p class="text-gray-600 mb-6"><?= __('Ihr Fehlerbericht wurde erfolgreich übermittelt.') ?></p>
                <button
                    type="button"
                    @click="closeForm()"
                    class="px-4 py-2 text-sm font-medium text-white bg-brand-deep rounded-md hover:bg-brand-dark"
                >
                    <?= __('Schließen') ?>
                </button>
            </div>

            <!-- Form -->
            <form x-show="!success" @submit.prevent="submitReport()" class="space-y-6" data-no-loading>
                <!-- Error Message -->
                <div x-show="error" x-cloak class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800" x-text="error"></p>
                        </div>
                    </div>
                </div>

                <!-- Description Field -->
                <div>
                    <label for="bug-report-description" class="block text-sm font-medium text-gray-700 mb-2">
                        <?= __('Beschreibung') ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="bug-report-description"
                        x-model="description"
                        x-ref="descriptionInput"
                        rows="16"
                        placeholder="<?= __('Detaillierte Beschreibung des Fehlers...') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-brand-deep focus:border-brand-deep text-sm font-mono"
                        :disabled="loading"
                        required
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <?= __('Die System-Informationen sind bereits ausgefüllt. Bitte ergänzen Sie die Beschreibung.') ?>
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                    <button
                        type="button"
                        @click="closeForm()"
                        :disabled="loading"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-deep disabled:opacity-50"
                    >
                        <?= __('Abbrechen') ?>
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-brand-deep rounded-lg hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-deep disabled:opacity-50"
                    >
                        <span x-show="loading" class="mr-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <?= __('Absenden') ?>
                    </button>
                </div>
            </form>
<?php if (!$isAjax): ?>
        </div>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<script>
function bugReportForm(isModal = false) {
    return {
        loading: false,
        success: false,
        error: null,
        isModal: isModal,
        description: <?= json_encode($defaultDescription) ?>,

        init() {
            // Add browser info to description
            const browserInfo = this.getBrowserInfo();
            this.description = this.description.replace(
                '(wird automatisch ausgefüllt)',
                browserInfo
            );

            // Focus description textarea
            this.$nextTick(() => {
                this.$refs.descriptionInput?.focus();
            });
        },

        getBrowserInfo() {
            const ua = navigator.userAgent;
            let browser = 'Unknown';
            if (ua.includes('Firefox/')) {
                browser = 'Firefox ' + ua.split('Firefox/')[1].split(' ')[0];
            } else if (ua.includes('Chrome/') && !ua.includes('Edg/')) {
                browser = 'Chrome ' + ua.split('Chrome/')[1].split(' ')[0];
            } else if (ua.includes('Edg/')) {
                browser = 'Edge ' + ua.split('Edg/')[1].split(' ')[0];
            } else if (ua.includes('Safari/') && !ua.includes('Chrome')) {
                browser = 'Safari ' + (ua.split('Version/')[1]?.split(' ')[0] || '');
            }
            return browser;
        },

        generateTitle() {
            const now = new Date();
            const pad = (n) => n.toString().padStart(2, '0');
            const date = `${pad(now.getDate())}.${pad(now.getMonth() + 1)}.${now.getFullYear()}`;
            const time = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
            return `[Bug Report] ${date} ${time}`;
        },

        async submitReport() {
            if (!this.description.trim()) {
                this.error = '<?= __('Bitte geben Sie eine Beschreibung ein.') ?>';
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const response = await fetch('<?= $this->Url->build(['controller' => 'BugReports', 'action' => 'add']) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': document.querySelector('meta[name=csrfToken]')?.content || ''
                    },
                    body: new URLSearchParams({
                        title: this.generateTitle(),
                        description: this.description
                    }),
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    this.success = true;
                } else {
                    this.error = data.error || '<?= __('Ein Fehler ist aufgetreten.') ?>';
                }
            } catch (err) {
                console.error('Bug report submission failed:', err);
                this.error = '<?= __('Verbindungsfehler. Bitte versuchen Sie es erneut.') ?>';
            } finally {
                this.loading = false;
            }
        },

        closeForm() {
            if (this.isModal) {
                // Close the modal by dispatching the close event
                window.dispatchEvent(new CustomEvent('close-modal-bug-report-modal'));
            } else {
                // Close the window (standalone page mode)
                window.close();
            }
        }
    };
}
</script>
