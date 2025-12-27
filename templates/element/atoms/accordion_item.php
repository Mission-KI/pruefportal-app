<?php
/**
 * Accordion Item Atom
 *
 * A single collapsible section with header and content.
 * Uses Alpine.js for interactivity and TailwindCSS for styling.
 *
 * @param string $id - Unique identifier for the accordion item
 * @param string $title - Header text for the accordion
 * @param string $content - Body content (can be HTML if escape=false)
 * @param bool $open - Whether the accordion should start open (default: false)
 * @param bool $escape - Whether to escape HTML in content (default: true)
 * @param array $options - Additional HTML attributes for the container
 */

$id = $id ?? uniqid('accordion_item_');
$title = $title ?? '';
$content = $content ?? '';
$open = $open ?? false;
$escape = $escape ?? true;
$options = $options ?? [];

// Prepare container attributes
$containerAttributes = [
    'x-data' => '{open: ' . ($open ? 'true' : 'false') . '}',
    'class' => 'mki-accordion-item ' . ($options['class'] ?? '')
];

// Merge additional options
$containerAttributes = array_merge($containerAttributes, array_diff_key($options, ['class' => '']));
?>

<div <?= $this->Html->templater()->formatAttributes($containerAttributes) ?>>
    <!-- Accordion Header -->
    <button
        type="button"
        class="mki-accordion-header"
        @click="open = !open"
        :aria-expanded="open"
        :aria-controls="'<?= $id ?>-content'"
        id="<?= $id ?>-header"
    >
        <span><?= h($title) ?></span>

        <!-- Chevron Icon -->
        <div class="flex-shrink-0 ml-2">
            <?= $this->element('atoms/icon', [
                'name' => 'chevron-down',
                'size' => 'sm',
                'options' => [
                    'class' => 'mki-accordion-chevron',
                    ':class' => "{'mki-accordion-chevron--open': open}"
                ]
            ]) ?>
        </div>
    </button>

    <!-- Accordion Content -->
    <div
        class="mki-accordion-content"
        x-show="open"
        x-collapse
        id="<?= $id ?>-content"
        role="region"
        :aria-labelledby="'<?= $id ?>-header'"
    >
        <div class="mki-accordion-content-inner">
            <?php if ($escape): ?>
                <?= h($content) ?>
            <?php else: ?>
                <?= $content ?>
            <?php endif; ?>
        </div>
    </div>
</div>