<?php
/**
 * Responsive Tooltip/Bottomsheet Atom Component
 *
 * Mobile-native help component that adapts based on viewport:
 * - Desktop (≥768px): Hover/focus-triggered popover with collision detection
 * - Mobile (<768px): Tap-triggered slide-up bottomsheet
 *
 * Replaces duplicated tooltip code in form_field.php with WCAG 2.1 compliant implementation.
 *
 * @var \App\View\AppView $this
 * @var string $content Required: Tooltip/help text content
 * @var string $id Required: Unique ID for ARIA association
 * @var string|null $title Optional: Sheet title (mobile only)
 * @var string $triggerLabel Optional: ARIA label for trigger button (default: "More information")
 * @var string $icon Optional: Icon name (default: help-circle)
 * @var string $iconSize Optional: Icon size sm|md|lg (default: sm)
 * @var string $placement Optional: Desktop placement top|bottom (default: bottom)
 */

$content = $content ?? '';
$id = $id ?? 'tooltip-' . uniqid();
$title = $title ?? null;
$triggerLabel = $triggerLabel ?? __('More information');
$icon = $icon ?? 'help-circle';
$iconSize = $iconSize ?? 'sm';
$placement = $placement ?? 'bottom';

if (empty($content)) {
    return;
}
?>

<div x-data="{
    open: false,
    isMobile: false,
    position: 'center',
    swipeStartY: 0,
    swipeCurrentY: 0,

    init() {
        this.$nextTick(() => {
            this.updateDeviceType();
        });
        window.addEventListener('resize', () => this.updateDeviceType());
    },

    updateDeviceType() {
        const breakpoint = getComputedStyle(document.documentElement)
            .getPropertyValue('--breakpoint-sm').trim();
        const breakpointValue = breakpoint || '768px';
        this.isMobile = !window.matchMedia(`(min-width: ${breakpointValue})`).matches;
    },

    toggle() {
        this.open = !this.open;
        if (this.open && !this.isMobile) {
            this.$nextTick(() => this.adjustPosition());
        }
        if (this.open && this.isMobile) {
            this.lockBodyScroll();
        } else {
            this.unlockBodyScroll();
        }
    },


    adjustPosition() {
        const tooltip = this.$refs.tooltip;
        if (!tooltip) return;

        const rect = tooltip.getBoundingClientRect();
        const viewportWidth = window.innerWidth;

        if (rect.left < 0) {
            this.position = 'left';
        } else if (rect.right > viewportWidth) {
            this.position = 'right';
        } else {
            this.position = 'center';
        }
    },

    handleSwipeStart(e) {
        this.swipeStartY = e.touches[0].clientY;
    },

    handleSwipeMove(e) {
        this.swipeCurrentY = e.touches[0].clientY;
    },

    handleSwipeEnd() {
        const swipeDistance = this.swipeCurrentY - this.swipeStartY;
        if (swipeDistance > 100) {
            this.close();
        }
    },

    close() {
        this.open = false;
        this.unlockBodyScroll();
    },

    lockBodyScroll() {
        document.body.style.overflow = 'hidden';
    },

    unlockBodyScroll() {
        document.body.style.overflow = '';
    }
}" class="inline-block relative">

    <button
        type="button"
        @click="toggle"
        @keydown.escape="close"
        aria-describedby="<?= h($id) ?>"
        aria-label="<?= h($triggerLabel) ?>"
        class="tooltip-trigger inline-flex items-center justify-center p-1 text-gray-500 hover:text-brand-light-web focus-visible:text-brand-light-web focus-visible:outline-2 focus-visible:outline-brand-light-web transition-colors">
        <?= $this->element('atoms/icon', [
            'name' => $icon,
            'size' => $iconSize
        ]) ?>
    </button>

    <div
        x-show="open && !isMobile"
        x-ref="tooltip"
        @click.away="close"
        @keydown.escape="close"
        x-transition
        role="tooltip"
        id="<?= h($id) ?>"
        class="absolute bottom-full mb-2 z-[9999] bg-brand-deep text-white p-4 rounded-[var(--radius-md)] shadow-[var(--shadow-lg)] min-w-64 max-w-[min(32rem,calc(100vw-2rem))]"
        :class="{
            'left-1/2 -translate-x-1/2': position === 'center',
            'left-0': position === 'left',
            'right-0': position === 'right'
        }"
        style="display: none;">

        <div class="absolute top-full w-0 h-0 border-l-[0.5rem] border-l-transparent border-r-[0.5rem] border-r-transparent border-t-[0.5rem] border-t-brand-deep"
             :class="{
                'left-1/2 -translate-x-1/2': position === 'center',
                'left-4': position === 'left',
                'right-4': position === 'right'
             }"></div>

        <div class="text-sm leading-normal">
            <?= h($content) ?>
        </div>
    </div>

    <div
        x-show="open && isMobile"
        @click="close"
        x-transition:enter="transition-opacity duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-250"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="tooltip-backdrop fixed inset-0 bg-black/50 z-[9998]"
        style="display: none;">
    </div>

    <div
        x-show="open && isMobile"
        @touchstart="handleSwipeStart"
        @touchmove="handleSwipeMove"
        @touchend="handleSwipeEnd"
        @keydown.escape="close"
        role="dialog"
        :aria-labelledby="<?= !empty($title) ? "'$id-title'" : 'null' ?>"
        aria-describedby="<?= h($id) ?>-content"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-250"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="tooltip-bottomsheet fixed bottom-0 left-0 right-0 z-[9999] bg-brand-deep text-white rounded-t-[var(--radius-card-lg)] p-4 max-h-[80vh] overflow-y-auto"
        style="display: none; padding-bottom: calc(1rem + env(safe-area-inset-bottom));">

        <div class="tooltip-drag-handle w-10 h-1 bg-white/30 rounded-full mx-auto mb-4"></div>

        <?php if (!empty($title)): ?>
        <h3 id="<?= h($id) ?>-title" class="text-lg font-semibold text-white mb-3">
            <?= h($title) ?>
        </h3>
        <?php endif; ?>

        <div id="<?= h($id) ?>-content" class="text-sm leading-normal">
            <?= h($content) ?>
        </div>
    </div>

</div>
