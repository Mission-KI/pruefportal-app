<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment $comments
 */
?>
<?php foreach ($comments as $comment): ?>
    <div class="comment">
        <div class="comment-header">
            <span class="text-muted"><?= $comment->created->format('Y-m-d H:i') ?></span><br>
            <strong><?= h($comment->user->full_name) ?></strong>
            <span class="border rounded px-2 py-1 text-sm mr-2"><?= $comment->reference_id > 0 ? $comment->reference_id : __('General'); ?></span>
        </div>
        <div class="comment-body">
            <?= $this->element('atoms/avatar', array (
                'initials' => $this->Layout->getInitials($comment->user->full_name),
                'full_name' => $comment->user->full_name,
                'size' => 'md',
            )) ?>
            <p><?= nl2br(h($comment->content)) ?></p>
        </div>
        <?php if (!empty($comment->child_comments)): ?>
            <div class="replies" style="margin-left: 30px;">
                <?= $this->element('comments_thread', ['comments' => $comment->child_comments]) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
