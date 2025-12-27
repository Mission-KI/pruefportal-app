<?php
/**
 * Form Card Molecule Component
 *
 * Specialized card component for forms with consistent header/footer structure.
 * Wraps form content in a Bootstrap-styled card container.
 *
 * @var \App\View\AppView $this
 * @var string $title Card title (required)
 * @var string $subtitle Optional subtitle text below title
 * @var string $form_content The complete form HTML content
 * @var array $footer_actions Array of footer button configurations
 * @var array $options Additional HTML attributes for the card container
 * @var bool $escape Whether to escape HTML content (default: false for forms)
 */

// Set defaults
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$form_content = $form_content ?? '';
$footer_actions = $footer_actions ?? [];
$options = $options ?? [];
$escape = $escape ?? false;

// Validate required fields
if (empty($title) || empty($form_content)) {
    return; // Don't render card without title and content
}

// Build base card classes
$cardClasses = [
    'card',
    'shadow-sm',
    'my-4'
];

// Add user-provided classes
if (isset($options['class'])) {
    $cardClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $cardClasses);
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <!-- Card Header -->
    <div class="card-header">
        <h1 class="h4 text-center"><?= h($title) ?></h1>
        <?php if ($subtitle): ?>
            <p class="text-muted"><?= h($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <!-- Card Body with Form Content -->
    <div class="card-body">
        <?= $escape ? h($form_content) : $form_content ?>
    </div>

    <?php if (!empty($footer_actions)): ?>
        <!-- Card Footer -->
        <div class="card-footer text-center">
            <?php foreach ($footer_actions as $action): ?>
                <?php if (is_array($action)): ?>
                    <?= $this->element('atoms/button', array_merge([
                        'size' => 'md',
                        'variant' => 'tertiary'
                    ], $action)) ?>
                <?php else: ?>
                    <?= $action ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>