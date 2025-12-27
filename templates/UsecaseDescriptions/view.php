<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var \App\Model\Entity\UsecaseDescription $usecaseDescription
 * @var \App\Controller\UsecaseDescriptionsController $maxStep
 * @var array $commentReferences
 * @var string $mode Display mode: 'view' or 'review' (set by controller)
 */
$title_for_layout = __('Process') . ': ' . $process->title;
$this->assign('title', $title_for_layout);

echo $this->Html->script('modal-helpers', [
    'block' => true,
    'type' => 'module'
]);

// Default mode to 'view' if not set
$mode = $mode ?? 'view';
?>
<h1 class="text-primary display-xs uppercase"><?= $process->title ?></h1>
<p class="text-gray-600 mb-6">
    <?= $mode === 'review' ? __('Review UCD') : __('UCD') ?> - <?= __('Version') ?> <?= $usecaseDescription->version ?>
</p>

<?= $this->element('process_status', ['process' => $process]); ?>

<?= $this->element('molecules/primary_card', [
    'title' => __('Ergebnis der Anwendungsfallbeschreibung'),
    'subtitle' => __('Anwendungsfallbeschreibung'),
    'body' => __('Die Anwendungsfallbeschreibung definiert den Gegenstand der Prüfung, die Zweckbestimmung und den Anwendungsbereich des KI-Systems. Sie bestimmt, welche Qualitätskriterien relevant sind, und bildet die Basis für die Schutzbedarfsanalyse sowie alle späteren Prüfentscheidungen.')
]) ?>

<?php $flatData = $usecaseDescription->getParsedDescription(); ?>

<?= $this->element('molecules/usecase_description_viewer_refactored', [
    'flatData' => $flatData,
    'mode' => $mode,
    'process' => $process,
    'commentReferences' => $commentReferences ?? [],
    'usecaseDescription' => $usecaseDescription
]) ?>

<?php if ($mode === 'review' && $process->status_id === 15): ?>
<div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-300">
    <?= $this->Form->postLink(
        __('Reject UCD'),
        ['controller' => 'UsecaseDescriptions', 'action' => 'reject', $usecaseDescription->id],
        [
            'confirm' => __('Confirm: Are you sure you want to reject the UCD for {0} back to the candidate?', h($process->title)),
            'class' => 'inline-flex items-center px-4 py-2 border border-primary shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
            'escape' => false
        ]
    ) ?>

    <?= $this->Form->postLink(
        __('Accept UCD'),
        ['controller' => 'UsecaseDescriptions', 'action' => 'accept', $usecaseDescription->id],
        [
            'confirm' => __('Confirm: Are you sure you accept the UCD for {0} from the candidate?', h($process->title)),
            'class' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand',
            'escape' => false
        ]
    ) ?>
</div>
<?php endif; ?>


