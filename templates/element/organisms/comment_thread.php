<?php
/**
 * Comment Thread Organism Component
 *
 * Recursively renders comment cards with threading support.
 * Handles nested replies with indentation.
 *
 * @var \App\View\AppView $this
 * @var array $comments Array of comment entities
 * @var int $depth Current nesting depth (default: 0)
 * @var bool $frame_applied Whether a frame has already been applied in this branch (default: false)
 */

$comments = $comments ?? [];
$depth = $depth ?? 0;
$frame_applied = $frame_applied ?? false;

// Validation: ensure $comments is an array
if (!is_array($comments)) {
    echo '<p class="text-gray-500 italic">Demo placeholder: Mock comment thread would appear here</p>';
    return;
}

// Indentation classes based on depth
$indentClass = $depth > 0 ? 'ml-4 md:ml-8' : '';
?>

<?php foreach ($comments as $i => $comment): ?>
<?php
    // Determine if this comment should have a frame
    $showFrame = ($comment->seen ?? false) && !$frame_applied;

    // If showing frame, mark it as applied for descendants
    $frameAppliedForChildren = $frame_applied || $showFrame;

    // Frame wrapper classes
    $wrapperClass = $showFrame ? 'bg-brand-lightest border border-brand-light rounded-lg p-5 mb-4' : '';
?>

    <?php if ($showFrame || $depth === 0): ?>
        <!-- Comment with frame (new) or top-level without frame (old at depth 0) -->
        <div class="<?= $wrapperClass ?> <?= $depth === 0 && !$showFrame ? 'mb-4' : '' ?>">
            <?= $this->element('molecules/comment_card', [
                'comment' => $comment,
                'depth' => $depth,
                'is_new' => $comment->is_new ?? false
            ]) ?>

            <?php if (!empty($comment->child_comments)): ?>
                <?= $this->element('organisms/comment_thread', [
                    'comments' => $comment->child_comments,
                    'depth' => $depth + 1,
                    'frame_applied' => $frameAppliedForChildren
                ]) ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Nested child comment with indentation only -->
        <div class="<?= $indentClass ?>">
            <?= $this->element('molecules/comment_card', [
                'comment' => $comment,
                'depth' => $depth,
                'is_new' => $comment->is_new ?? false
            ]) ?>

            <?php if (!empty($comment->child_comments)): ?>
                <?= $this->element('organisms/comment_thread', [
                    'comments' => $comment->child_comments,
                    'depth' => $depth + 1,
                    'frame_applied' => $frameAppliedForChildren
                ]) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php endforeach; ?>
