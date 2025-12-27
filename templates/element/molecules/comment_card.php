<?php
/**
 * Comment Card Molecule Component
 *
 * Single comment display with user info, timestamp, tag, and content.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment Comment entity with user relation
 * @var int $depth Nesting depth level (default: 0)
 * @var bool $is_new Whether this comment is new/unseen for current user (default: false)
 */

$comment = $comment ?? null;
$depth = $depth ?? 0;
$is_new = $is_new ?? false;

// Validate that we have a proper Comment entity object
if (!$comment) {
    return;
}

// Check if comment is a string (from demo/mock data) or invalid object
if (is_string($comment) || !is_object($comment)) {
    echo '<div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">';
    echo '<p class="text-yellow-800 text-sm font-semibold">Comment Card Component</p>';
    echo '<p class="text-yellow-700 text-xs mt-1">This component requires a real Comment entity object with loaded user relation.</p>';
    echo '<p class="text-gray-600 text-xs mt-2">Expected structure: Comment entity with properties: content, created, reference_id, user (relation)</p>';
    echo '</div>';
    return;
}

// Validate required properties exist
$requiredProps = ['content', 'created', 'user'];
$missingProps = [];
foreach ($requiredProps as $prop) {
    if (!isset($comment->$prop)) {
        $missingProps[] = $prop;
    }
}

if (!empty($missingProps)) {
    echo '<div class="p-4 bg-red-50 border border-red-200 rounded-lg">';
    echo '<p class="text-red-800 text-sm font-semibold">Invalid Comment Entity</p>';
    echo '<p class="text-red-700 text-xs mt-1">Missing required properties: ' . h(implode(', ', $missingProps)) . '</p>';
    echo '</div>';
    return;
}

// TODO: This logic should come from the entity or controller
$userRole = 'Prüfling'; // Default, could be determined by checking if user is examiner or candidate

$tagVariant = 'default';
$tagText = __('General');
if ($comment->reference_id && $comment->reference_id !== '0') {
    $tagVariant = 'info'; // Use info variant for specific references like DA.1, UCD.1-1
    $tagText = $comment->reference_id;
}

$cardClasses = [
    'bg-transparent',
    'p-5',
    'mb-4'
];

$cardClass = implode(' ', $cardClasses);
?>

<div class="<?= $cardClass ?>">
    <div class="flex items-start align-center mb-3 flex-wrap gap-2">
        <div class="flex items-center gap-3 flex-wrap">
            <?= $this->element('atoms/timestamp', [
                'datetime' => $comment->created,
                'options' => [
                    'class' => 'w-full'
                ]
            ]) ?>

            <?= $this->element('molecules/user_info', [
                'full_name' => $comment->user->full_name ?? 'Unknown User',
                'role' => $userRole,
                'orientation' => 'horizontal'
            ]) ?>

            <?= $this->element('atoms/tag', [
                'text' => $tagText,
                'variant' => $tagVariant
            ]) ?>
        </div>
    </div>

    <div class="flex gap-3">
        <?= $this->element('atoms/avatar', [
            'initials' => $this->Layout->getInitials($comment->user->full_name ?? 'Unknown User'),
            'full_name' => $comment->user->full_name ?? 'Unknown User',
            'size' => 'md',
            'online_status' => null // TODO: Add online status when available from backend
        ]) ?>

        <div class="flex-1 text-gray-900 leading-relaxed">
            <?= nl2br(h($comment->content)) ?>

            <?php if (isset($comment->uploads) && !empty($comment->uploads)): ?>
                <?= $this->element('molecules/attachments', [
                    'uploads' => $comment->uploads
                ]) ?>
            <?php endif; ?>
        </div>

    </div>
</div>
