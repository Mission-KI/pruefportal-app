<?php
/**
 * Documentation Link Molecule
 *
 * Displays a link to the external documentation for an indicator.
 *
 * @var \App\View\AppView $this
 * @var string $docs_id The documentation ID for the indicator
 */

if (empty($docs_id)) {
    return;
}
?>
<div class="mb-4 p-0">
    <a class="text-md font-semibold text-primary" href="https://docs.pruefportal.mission-ki.de/indicators/<?= h($docs_id) ?>" target="_blank">
        <?= $this->element('atoms/icon', ['name' => 'external-link', 'size' => 'sm', 'options' => ['class' => 'w-5 h-5']]) ?>
        <?= __('Dokumentation: Relevante Prüfmethoden & weitere Informationen') ?>
    </a>
</div>
