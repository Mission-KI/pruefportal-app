<?php
/**
 * Criterion Row Molecule (Desktop Table Row)
 *
 * @var \App\View\AppView $this
 * @var array $criterion Criterion data with keys:
 *   - string 'index': Criterion identifier (e.g., 'DA1', 'TR2')
 *   - string 'name': Human-readable criterion name
 *   - int|bool|null 'protectionLevel': Protection level (1=gering, 2=moderat, 3+=hoch, bool/null=N/A)
 *   - string|null 'classification': Classification value (A, B, C, D, N/A)
 *   - string|null 'classificationCandidate': Optional candidate classification for dual display
 *   - string|null 'fulfillment': Fulfillment status (ja, nein, N/A)
 * @var string $qdKey The QD key for accordion binding
 * @var bool $showIndicatorColumns Whether to show classification and fulfillment columns
 * @var bool $accordionMode Whether accordion transitions are enabled
 */

$showIndicatorColumns = $showIndicatorColumns ?? false;
$accordionMode = $accordionMode ?? false;
$transitionEnterDuration = 200;
$transitionLeaveDuration = 150;
?>

<tr class="m-0 p-0 <?= $showIndicatorColumns ? 'vcio-criterion-row' : '' ?>"
    <?php if ($showIndicatorColumns): ?>
    data-fulfillment="<?= h($criterion['fulfillment'] ?? 'N/A') ?>"
    data-criterion-index="<?= h($criterion['index']) ?>"
    data-criterion-name="<?= h($criterion['name']) ?>"
    <?php endif; ?>
    <?php if ($accordionMode): ?>
    x-show="openDimensions.includes('<?= h($qdKey) ?>')"
    x-transition:enter="transition-opacity duration-<?= $transitionEnterDuration ?>"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-<?= $transitionLeaveDuration ?>"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    <?php endif; ?>>
    <td class="px-6 py-4 whitespace-nowrap text-md font-medium text-gray-900">
        <span class="mki-form-field-index-badge"><?= h($criterion['index']) ?></span>
    </td>
    <td class="px-6 py-4 text-md text-gray-700">
        <?= h($criterion['name']) ?>
    </td>
    <td class="px-6 py-4 text-center whitespace-nowrap text-md">
        <?= $this->element('atoms/protection_level_badge', [
            'level' => $criterion['protectionLevel']
        ]) ?>
    </td>
    <?php if ($showIndicatorColumns): ?>
        <td class="px-6 py-4 text-center whitespace-nowrap text-md space-x-1">
            <?php if (isset($criterion['classificationCandidate'])): ?>
                <?= $this->element('atoms/classification_badge', [
                    'value' => $criterion['classificationCandidate'],
                    'outline' => true,
                    'title' => __('Selbsteinschätzung'),
                ]) ?>
            <?php endif; ?>
            <?= $this->element('atoms/classification_badge', [
                'value' => $criterion['classification'] ?? 'N/A',
                'title' => isset($criterion['classificationCandidate']) ? __('Validierung') : null,
            ]) ?>
        </td>
        <td class="px-6 py-4 text-center whitespace-nowrap text-md">
            <?= $this->element('atoms/fulfillment_badge', [
                'value' => $criterion['fulfillment'] ?? 'N/A'
            ]) ?>
        </td>
    <?php endif; ?>
</tr>
