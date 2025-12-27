<?php
/**
 * Quality Dimensions Table Component
 *
 * Unified table for displaying criteria and indicators organized by quality dimensions.
 * Supports both protection needs analysis (3 columns) and VCIO indicators (5 columns).
 *
 * @var \App\View\AppView $this
 * @var array $qualityDimensionsData Normalized data keyed by QD code
 * @var bool $showIndicatorColumns Show Einstufung + Erfüllt columns (default: false)
 * @var bool $showEditButtons Show edit button row (default: false)
 * @var bool $accordionMode Enable collapsible sections (default: false)
 */

use App\Model\Enum\QualityDimension;

$data = $qualityDimensionsData ?? [];
$showIndicatorColumns = $showIndicatorColumns ?? false;
$showEditButtons = $showEditButtons ?? false;
$accordionMode = $accordionMode ?? false;
$currentLanguage = $currentLanguage ?? 'de';

$colspan = $showIndicatorColumns ? 5 : 3;
?>

<div <?php if ($accordionMode): ?>x-data="{ openDimensions: [] }"<?php endif; ?> class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden md:block">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider"><?= __('Kriterium') ?></th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider"><?= __('Name') ?></th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider"><?= __('Schutzbedarf') ?></th>
                    <?php if ($showIndicatorColumns): ?>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                            <?= __('Einstufung') ?>
                            <span class="block text-xs font-normal normal-case"><?= __('(Selbsteinschätzung/Validierung)') ?></span>
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider"><?= __('Erfüllt?') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($data as $qdKey => $qdData):
                    $qd = QualityDimension::tryFrom($qdKey);
                    if (!$qd || empty($qdData['criteria'])) continue;
                ?>
                    <?= $this->element('molecules/qd_accordion_header', [
                        'qualityDimension' => $qd,
                        'qdKey' => $qdKey,
                        'currentLanguage' => $currentLanguage,
                        'accordionMode' => $accordionMode,
                        'colspan' => $colspan,
                        'isTableRow' => true,
                    ]) ?>

                    <?php if ($showEditButtons): ?>
                        <?= $this->element('organisms/quality_dimensions_table_edit_row', [
                            'qdKey' => $qdKey,
                            'qdData' => $qdData,
                            'showIndicatorColumns' => $showIndicatorColumns,
                            'accordionMode' => $accordionMode,
                        ]) ?>
                    <?php endif; ?>

                    <?php foreach ($qdData['criteria'] as $criterion): ?>
                        <?= $this->element('molecules/criterion_row', [
                            'criterion' => $criterion,
                            'qdKey' => $qdKey,
                            'showIndicatorColumns' => $showIndicatorColumns,
                            'accordionMode' => $accordionMode,
                        ]) ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden divide-y divide-gray-200">
        <?php foreach ($data as $qdKey => $qdData):
            $qd = QualityDimension::tryFrom($qdKey);
            if (!$qd || empty($qdData['criteria'])) continue;
        ?>
            <?= $this->element('molecules/qd_accordion_header', [
                'qualityDimension' => $qd,
                'qdKey' => $qdKey,
                'currentLanguage' => $currentLanguage,
                'accordionMode' => $accordionMode,
                'isTableRow' => false,
            ]) ?>

            <?php if ($showEditButtons && (!empty($qdData['editUrl']) || !empty($qdData['indicatorEditUrl']))): ?>
                <div class="bg-gray-50 px-4 py-2 flex gap-2 justify-end"
                    <?php if ($accordionMode): ?>x-show="openDimensions.includes('<?= h($qdKey) ?>')" x-transition<?php endif; ?>>
                    <?php if (!empty($qdData['indicatorEditUrl']) && $showIndicatorColumns): ?>
                        <?= $this->element('atoms/button', [
                            'label' => __('Bearbeiten'),
                            'variant' => 'primary',
                            'size' => 'XS',
                            'url' => $qdData['indicatorEditUrl']
                        ]) ?>
                    <?php elseif (!empty($qdData['editUrl']) && !$showIndicatorColumns): ?>
                        <?= $this->element('atoms/button', [
                            'label' => __('Bearbeiten'),
                            'variant' => 'primary',
                            'size' => 'XS',
                            'url' => $qdData['editUrl']
                        ]) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php foreach ($qdData['criteria'] as $criterion): ?>
                <?= $this->element('molecules/criterion_card', [
                    'criterion' => $criterion,
                    'qdKey' => $qdKey,
                    'showIndicatorColumns' => $showIndicatorColumns,
                    'accordionMode' => $accordionMode,
                ]) ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
