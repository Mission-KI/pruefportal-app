<?php
/**
 * Modal Component (Molecule)
 *
 * A fully accessible modal dialog built with Tailwind 4 and Alpine.js.
 * Supports AJAX content loading, form submissions, and customizable appearance.
 *
 * @var \App\View\AppView $this
 * @var string $id Modal ID (required, must be unique)
 * @var string $title Modal title
 * @var string $content Modal body content
 * @var string $size Modal size: sm|md|lg|xl|full (default: md)
 * @var bool $closeable Whether modal can be closed (default: true)
 * @var bool $escape Whether to escape HTML in content (default: true)
 * @var array $footer Footer content/buttons
 * @var array $options Additional HTML attributes for modal container
 */

// use App\Utility\Icon;

$id = $id ?? 'modal-' . uniqid();
$title = $title ?? '';
$content = $content ?? '';
$size = $size ?? 'md';
$closeable = $closeable ?? true;
$escape = $escape ?? true;
$footer = $footer ?? [];
$options = $options ?? [];

$sizeClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    'full' => 'max-w-full mx-4'
];

$modalSize = $sizeClasses[$size] ?? $sizeClasses['md'];
?>

<div
    x-data="{
        open: false,
        loading: false,
        error: null,

        show() {
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        hide() {
            this.open = false;
            document.body.style.overflow = '';
            this.error = null;
        },

        async loadContent(url, referenceId = '') {
            this.loading = true;
            this.error = null;
            this.show();

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml',
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Failed to load content');
                }

                // Check if we were redirected to login page (session expired)
                if (response.url.includes('/login')) {
                    // Redirect to login with current page as redirect target (not the AJAX route)
                    const loginUrl = new URL(response.url);
                    loginUrl.searchParams.set('redirect', window.location.pathname);
                    window.location.href = loginUrl.toString();
                    return;
                }

                const html = await response.text();
                this.$refs.modalBody.innerHTML = html;

                // Dispatch event for reference population
                window.dispatchEvent(new CustomEvent('modal-content-loaded', {
                    detail: {
                        modalId: this.$el.id,
                        modalBody: this.$refs.modalBody,
                        referenceId: referenceId
                    }
                }));
            } catch (error) {
                console.error('Error loading modal content:', error);
                this.error = 'Failed to load content. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    }"
    x-on:keydown.escape.window="<?= $closeable ? 'hide()' : '' ?>"
    x-on:open-modal-<?= h($id) ?>.window="show()"
    x-on:close-modal-<?= h($id) ?>.window="hide()"
    x-on:load-modal-<?= h($id) ?>.window="loadContent($event.detail.url, $event.detail.referenceId || '')"
    id="<?= h($id) ?>"
    <?= $this->Html->templater()->formatAttributes($options) ?>
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="<?= $closeable ? 'hide()' : '' ?>"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50"
        style="display: none;"
    ></div>

    <!-- Modal -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        @click="<?= $closeable ? 'hide()' : '' ?>"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                @click.stop
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full <?= $modalSize ?>"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title-<?= h($id) ?>"
            >
                <!-- Header -->
                <?php if ($title || $closeable): ?>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 id="modal-title-<?= h($id) ?>" class="text-lg font-semibold text-gray-900">
                        <?= $escape ? h($title) : $title ?>
                    </h3>
                    <?php if ($closeable): ?>
                    <button
                        type="button"
                        @click="hide()"
                        class="text-gray-400 hover:text-gray-500 transition-colors text-2xl leading-none"
                        aria-label="<?= __('Close') ?>"
                    >
                        &times;
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Body -->
                <div class="px-6 py-4" x-ref="modalBody">
                    <!-- Loading State -->
                    <div x-show="loading" class="flex items-center justify-center py-8">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-light border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status">
                            <span class="sr-only"><?= __('Loading...') ?></span>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div x-show="error" class="rounded-lg bg-error-50 border border-error-200 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="text-error-600 font-bold text-xl">!</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-error-800" x-text="error"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div x-show="!loading && !error">
                        <?= $escape ? h($content) : $content ?>
                    </div>
                </div>

                <!-- Footer -->
                <?php if (!empty($footer)): ?>
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3 bg-gray-50">
                    <?php foreach ($footer as $button): ?>
                        <?= $this->element('atoms/button', $button) ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
