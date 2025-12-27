<?php
/**
 * External Link Atom
 *
 * Renders a link with an external-link icon prefix
 *
 * @var \App\View\AppView $this
 * @var string $url The URL to link to
 * @var string $text The link text
 * @var array $options Additional HTML attributes for the anchor tag
 */

$url = $url ?? '#';
$text = $text ?? '';
$options = $options ?? [];

// Add default classes if not specified
if (!isset($options['class'])) {
    $options['class'] = 'inline-flex items-center gap-2';
}

// Always open in new tab for external links
$options['target'] = '_blank';
$options['rel'] = 'noopener noreferrer';
?>
<a href="<?= h($url) ?>"<?= $this->Html->templater()->formatAttributes($options) ?>>
    <?= $this->element('atoms/icon', ['name' => 'external-link', 'size' => 'sm', 'options' => ['class' => 'w-5 h-5', 'target' => '_blank']]) ?>
    <span><?= h($text) ?></span>
</a>
