<?php
/**
 * Quality Dimension Navigation Molecule
 *
 * Displays a flat list navigation for quality dimensions.
 * Completed dimensions show a check icon and are disabled.
 * Incomplete dimensions are clickable and continue from where the user left off.
 * All business logic (completion, URLs) is handled in the controller - this component is purely presentational.
 *
 * @var \App\View\AppView $this
 * @var \App\Controller\AppController $currentLanguage
 * @var array $qualityDimensions Array of quality dimensions from protectionNeedsAnalysis
 * @var string $currentQdId Currently active quality dimension ID
 * @var int $processId Process ID for "Übersicht" link
 * @var array $navigationState Navigation state per QD: ['qd_id' => ['isComplete' => bool, 'url' => array]]
 */

$qualityDimensions = $qualityDimensions ?? [];
$currentQdId = $currentQdId ?? '';
$processId = $processId ?? 0;
$navigationState = $navigationState ?? [];
?>

<nav>
    <!-- Übersicht Link -->
    <?= $this->Html->link(
        __('Übersicht'),
        ['controller' => 'Criteria', 'action' => 'index', $processId],
        ['class' => 'block px-3 py-2 text-sm font-medium text-brand hover:bg-gray-50 rounded-lg mb-2 underline']
    ) ?>

    <!-- Quality Dimensions List -->
    <div class="space-y-1">
        <?php foreach ($qualityDimensions as $qd_id => $qd): ?>
            <?php
            $isCurrent = $qd_id === $currentQdId;
            $qdState = $navigationState[$qd_id] ?? ['isComplete' => false, 'url' => null];
            ?>

            <?php if ($qdState['isComplete']): ?>
                <!-- Complete: Disabled span with check icon after label -->
                <span class="flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed rounded-lg">
                    <span><?= h($qd['title'][$currentLanguage]) ?> (<?= h($qd_id) ?>)</span>
                    <?= $this->element('atoms/icon', ['name' => 'check-circle', 'size' => 'sm', 'options' => ['class' => 'text-success-500']]) ?>
                </span>
            <?php else: ?>
                <!-- Incomplete: Clickable link -->
                <?php
                $linkClass = 'block px-3 py-2 text-sm font-medium rounded-lg transition-colors ';
                $linkClass .= $isCurrent ? 'bg-blue-50' : 'hover:bg-gray-50';

                $textClass = $isCurrent ? 'text-brand-deep' : 'text-brand';
                ?>
                <?= $this->Html->link(
                    '<span class="' . $textClass . '">' . h($qd['title'][$currentLanguage]) . ' (' . h($qd_id) . ')</span>',
                    $qdState['url'],
                    ['class' => $linkClass, 'escape' => false]
                ) ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</nav>
