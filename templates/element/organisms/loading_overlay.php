<?php
/**
 * Loading Overlay Organism
 *
 * Full-screen overlay with spinner for preventing double-clicks and showing loading state
 * Controlled by Alpine.js global state
 */
?>
<div x-data="{ show: false }"
     x-show="show"
     x-cloak
     @show-loading.window="show = true"
     @hide-loading.window="show = false"
     class="fixed inset-0 z-50 cursor-wait"
     style="display: none;">
</div>
