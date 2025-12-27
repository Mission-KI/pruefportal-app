<?php
/**
 * Quality Dimensions Table Edit Row
 *
 * @var string $qdKey
 * @var array $qdData
 * @var bool $showIndicatorColumns
 * @var bool $accordionMode
 */

$transitionEnterDuration = 200;
$transitionLeaveDuration = 150;
?>
<tr class="bg-gray-50"
    <?php if ($accordionMode): ?>
    x-show="openDimensions.includes('<?= h($qdKey) ?>')"
    x-transition:enter="transition-opacity duration-<?= $transitionEnterDuration ?>"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-<?= $transitionLeaveDuration ?>"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    <?php endif; ?>>
    <td class="px-6 py-3"></td>
    <td class="px-6 py-3"></td>
    <td class="px-6 py-3 text-center">
        <?php if (!empty($qdData['editUrl']) && !$showIndicatorColumns): ?>
            <?= $this->element('atoms/button', [
                'label' => __('Bearbeiten'),
                'variant' => 'primary',
                'size' => 'XS',
                'url' => $qdData['editUrl']
            ]) ?>
        <?php endif; ?>
    </td>
    <?php if ($showIndicatorColumns): ?>
        <td class="px-6 py-3 text-center">
            <?php if (!empty($qdData['indicatorEditUrl'])): ?>
                <?= $this->element('atoms/button', [
                    'label' => __('Bearbeiten'),
                    'variant' => 'primary',
                    'size' => 'XS',
                    'url' => $qdData['indicatorEditUrl']
                ]) ?>
            <?php endif; ?>
        </td>
        <td class="px-6 py-3"></td>
    <?php endif; ?>
</tr>
