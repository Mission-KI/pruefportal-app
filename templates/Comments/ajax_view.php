<?php
/**
 * @var iterable<\App\Model\Entity\Comment $comments
 */
$commentsArray = iterator_to_array($comments);
usort($commentsArray, function($a, $b) {
    $diff = $a->created->timestamp - $b->created->timestamp;
    if ($diff === 0) {
        return $a->id - $b->id;
    }
    return $diff;
});
?>
<?php foreach ($commentsArray as $comment): ?>
<div class="flex gap-2">
    <?php if($comment->hasValue('parent_id')): ?>
        <div style="width: 35px"><span class="hidden"><?= $comment->parent_id ?></span></div>
    <?php endif; ?>
    <?= $this->element('atoms/avatar', array (
        'initials' => $this->Layout->getInitials($comment->user->full_name),
        'full_name' => $comment->user->full_name,
        'size' => 'md',
    )) ?>
    <div class="flex-1">
        <?php if(!$comment->hasValue('parent_id')): ?>
            <span class="border rounded px-2 py-1 text-sm mr-2"><?= $comment->reference_id > 0 ? $comment->reference_id : __('General'); ?></span>
        <?php endif; ?>
        <strong class="text-primary"><?= h($comment->created->timeAgoInWords(['format' => 'MMM d, YYY', 'end' => '+1 year'])) ?></strong>
        <p class="my-2">
            <?= nl2br($comment->content); ?>
        </p>
    </div>
</div>
<?php endforeach; ?>
<div x-data="{ showForm: false }">
    <div class="mt-6 flex justify-end">
        <button class="btn btn-primary btn-sm"
                type="button"
                @click="showForm = true"
                x-show="!showForm"><?= __('Answer') ?></button>
    </div>

    <div x-show="showForm" x-cloak>
        <?= $this->element('../Comments/answer') ?>
    </div>
</div>
