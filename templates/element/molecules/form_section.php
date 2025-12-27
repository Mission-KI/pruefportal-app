<?php
/**
 * Form Section Molecule
 *
 * Renders the header section for each form step including:
 * - Category badge
 * - Step title
 * - Step description
 *
 * @var string $category Step category
 * @var string $title Step title
 * @var string $description Step description
 */
?>
<?php if (!empty($category) || !empty($title) || !empty($description)): ?>

<div class="pb-10">
    <?= $this->element('molecules/primary_card', [
        'title' => $title,
        'subtitle' => $category,
        'subtitle_position' => 'above',
        'body' => $this->Text->autoParagraph(h($description)),
        'escape' => false  // Allow HTML in body since autoParagraph generates HTML,
    ]) ?>
</div>
<?php endif; ?>
