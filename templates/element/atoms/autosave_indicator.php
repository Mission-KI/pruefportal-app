<?php
/**
 * Autosave Indicator Atom
 *
 * Provides hidden icon templates for auto-save feedback states.
 * Include this element once per form, then clone the templates via JavaScript.
 *
 * States:
 * - saving: spinning refresh icon (gray)
 * - success: check icon (success-700 green)
 * - error: alert-triangle icon (error-700 red)
 *
 * Usage in JavaScript:
 *   const template = document.getElementById('autosave-icon-saving');
 *   const indicator = template.cloneNode(true);
 *   indicator.removeAttribute('id');
 *   container.appendChild(indicator);
 *
 * @var \App\View\AppView $this
 * @var string $prefix Optional prefix for template IDs (default: 'autosave-icon')
 * @var string $className Optional CSS class for indicator spans (default: 'autosave-indicator')
 */

$prefix = $prefix ?? 'autosave-icon';
$className = $className ?? 'autosave-indicator';
?>
<div class="autosave-indicator-templates" style="display: none;" aria-hidden="true">
    <span id="<?= h($prefix) ?>-saving" class="<?= h($className) ?> inline-flex items-center">
        <?= $this->element('atoms/icon', [
            'name' => 'refresh',
            'size' => 'sm',
            'spin' => true,
            'options' => ['class' => 'text-gray-500']
        ]) ?>
    </span>
    <span id="<?= h($prefix) ?>-success" class="<?= h($className) ?> inline-flex items-center">
        <?= $this->element('atoms/icon', [
            'name' => 'check',
            'size' => 'sm',
            'options' => ['class' => 'text-success-700']
        ]) ?>
    </span>
    <span id="<?= h($prefix) ?>-error" class="<?= h($className) ?> inline-flex items-center">
        <?= $this->element('atoms/icon', [
            'name' => 'alert-triangle',
            'size' => 'sm',
            'options' => ['class' => 'text-error-700']
        ]) ?>
    </span>
</div>
