<?php
/**
 * Quality Dimension Accordion Header Molecule
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Enum\QualityDimension $qualityDimension The QD enum case
 * @var string $qdKey The QD key (e.g., 'DA', 'ND')
 * @var string $currentLanguage Current language code
 * @var bool $accordionMode Whether accordion is enabled
 * @var int $colspan Number of columns to span (for table context)
 * @var bool $isTableRow Whether to render as <tr> or <div> (default: true)
 */

use App\Model\Enum\QualityDimension;

$accordionMode = $accordionMode ?? false;
$colspan = $colspan ?? 3;
$isTableRow = $isTableRow ?? true;
$currentLanguage = $currentLanguage ?? 'de';
?>

<?php if ($isTableRow): ?>
<tr class="bg-gray-100 <?= $accordionMode ? 'cursor-pointer' : '' ?>"
    <?php if ($accordionMode): ?>
    <?php // Safe to embed $qdKey - values are enum-constrained (DA, ND, TR, MA, VE, CY) ?>
    @click="openDimensions.includes('<?= h($qdKey) ?>')
        ? openDimensions = openDimensions.filter(d => d !== '<?= h($qdKey) ?>')
        : openDimensions.push('<?= h($qdKey) ?>')"
    <?php endif; ?>>
    <td colspan="<?= $colspan ?>" class="px-6 py-4">
<?php else: ?>
<div class="bg-gray-100 px-4 py-3 <?= $accordionMode ? 'cursor-pointer' : '' ?>"
    <?php if ($accordionMode): ?>
    <?php // Safe to embed $qdKey - values are enum-constrained (DA, ND, TR, MA, VE, CY) ?>
    @click="openDimensions.includes('<?= h($qdKey) ?>')
        ? openDimensions = openDimensions.filter(d => d !== '<?= h($qdKey) ?>')
        : openDimensions.push('<?= h($qdKey) ?>')"
    <?php endif; ?>>
<?php endif; ?>

        <div class="flex items-center gap-3">
            <?php if ($accordionMode): ?>
                <svg class="w-5 h-5 transition-transform duration-200 flex-shrink-0"
                     :class="openDimensions.includes('<?= $qdKey ?>') ? 'rotate-180' : ''"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            <?php endif; ?>

            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 <?= $isTableRow ? '' : 'w-8 h-8' ?>">
                <?= $this->element('atoms/icon', [
                    'name' => $qualityDimension->icon(),
                    'size' => $isTableRow ? 'md' : 'sm',
                    'options' => ['class' => 'text-primary']
                ]) ?>
            </div>

            <h3 class="text-lg font-semibold text-brand">
                <?= h($qualityDimension->label($currentLanguage)) ?>
                <span title="<?= $qualityDimension->id() ?>">(<?= h($qdKey) ?>)</span>
            </h3>
        </div>

<?php if ($isTableRow): ?>
    </td>
</tr>
<?php else: ?>
</div>
<?php endif; ?>
