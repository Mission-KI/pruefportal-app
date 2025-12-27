<?php
/**
 * Mobile Form Actions Molecule
 *
 * A wrapper for form action buttons that provides mobile-sticky behavior.
 * On mobile: fixed to bottom with shadow and safe-area padding.
 * On desktop: inline/relative with standard styling.
 *
 * @var \App\View\AppView $this
 * @var string $body The button elements to render inside the container
 * @var string $justify Flexbox justify content: 'between', 'end', 'center', 'start' (default: 'between')
 * @var string $class Additional CSS classes to apply
 */

$body = $body ?? '';
$justify = $justify ?? 'between';
$class = $class ?? '';

$justifyClass = match ($justify) {
    'end' => 'justify-end',
    'center' => 'justify-center',
    'start' => 'justify-start',
    default => 'justify-between',
};
?>
<div class="form-navigation mt-8 flex <?= $justifyClass ?> items-center gap-4
            md:relative md:bottom-auto md:left-auto md:right-auto md:w-auto md:shadow-none md:p-0 md:bg-transparent
            fixed bottom-0 left-0 right-0 p-4 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50
            pb-[calc(1rem+env(safe-area-inset-bottom))] <?= $class ?>">
    <?= $body ?>
</div>
