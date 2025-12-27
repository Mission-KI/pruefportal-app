<?php
/**
 * @var \App\View\AppView $this
 * @var int|null $process_id Currently selected process ID
 * @var array $processes Array of process options [id => title]
 * @var array $groupedComments Comments grouped by process: [process_id => ['process' => entity, 'comments' => array]]
 */
$this->assign('title', __('Kommentare'));
$this->assign('show_content_card', 'false'); // Disable outer card wrapper for this page

// Flatten grouped comments into a single array
$allComments = [];
foreach ($groupedComments as $group) {
    if (!empty($group['comments'])) {
        $allComments = array_merge($allComments, $group['comments']);
    }
}
?>

<div class="comments-page-container max-w-5xl">
    <!-- Header and Filter Card -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h1 class="text-xl font-semibold text-brand-deep mb-6"><?= __('Kommentare') ?></h1>
    <?php if (!empty($processes)): ?>
        <?= $this->element('molecules/process_filter_bar', [
            'redirect' => 'comments',
            'processes' => $processes,
            'process_id' => $process_id,
            'onChange' => '$el.submit()'
        ]) ?>
    <?php endif; ?>
    </div>

    <!-- Comments Thread -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <?php if (!empty($allComments)): ?>
            <?= $this->element('organisms/comment_thread', [
                'comments' => $allComments,
                'depth' => 0
            ]) ?>
        <?php else: ?>
            <p class="text-gray-500 lg:w-5xl w-full"><?= __('No comments found') ?></p>
        <?php endif; ?>
    </div>

<?php if (!empty($process_id)): // Only show new comment form if a process is selected ?>
    <!-- New Comment Form -->
    <div class="mt-6">
        <?= $this->element('molecules/comment_form', [
            'process_id' => $process_id,
            'reference_id' => null, // Empty for now - will be populated with criteria later
            'submit_url' => ['controller' => 'Comments', 'action' => 'add', $process_id]
        ]) ?>
    </div>
<?php endif; ?>
</div>
