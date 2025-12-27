<?php
/**
 * Criterion Card Molecule (Mobile Card View)
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
 * @var bool $showIndicatorColumns Whether to show classification and fulfillment fields
 * @var bool $accordionMode Whether accordion transitions are enabled
 */

$showIndicatorColumns = $showIndicatorColumns ?? false;
$accordionMode = $accordionMode ?? false;
$transitionEnterDuration = 200;
$transitionLeaveDuration = 150;
?>

<div class="p-4 <?= $showIndicatorColumns ? 'vcio-criterion-row' : '' ?>"
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
    <div class="space-y-2">
        <div class="flex items-center gap-2">
            <span class="mki-form-field-index-badge"><?= h($criterion['index']) ?></span>
            <span class="text-sm font-medium text-gray-900"><?= h($criterion['name']) ?></span>
        </div>

        <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
            <dt class="text-gray-500"><?= __('Schutzbedarf') ?></dt>
            <dd><?= $this->element('atoms/protection_level_badge', [
                'level' => $criterion['protectionLevel'],
                'showBackground' => false
            ]) ?></dd>

            <?php if ($showIndicatorColumns): ?>
                <dt class="text-gray-500">
                    <?= __('Einstufung') ?>
                    <?php if (isset($criterion['classificationCandidate'])): ?>
                        <span class="block text-xs"><?= __('(Selbsteinsch./Valid.)') ?></span>
                    <?php endif; ?>
                </dt>
                <dd class="space-x-1">
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
                </dd>

                <dt class="text-gray-500"><?= __('Erfüllt?') ?></dt>
                <dd><?= $this->element('atoms/fulfillment_badge', [
                    'value' => $criterion['fulfillment'] ?? 'N/A'
                ]) ?></dd>
            <?php endif; ?>
        </dl>
    </div>
</div>
