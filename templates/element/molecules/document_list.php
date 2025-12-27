<?php
/**
 * Document List Molecule
 *
 * Displays a list of uploaded documents with download links.
 * Reuses file_attachment atom pattern.
 *
 * @var \App\View\AppView $this
 * @var array $uploads Array of Upload entities
 * @var string $emptyMessage Message to show when no documents
 */

$uploads = $uploads ?? [];
$emptyMessage = $emptyMessage ?? __('No documents available');

if (empty($uploads)) {
    echo '<p class="text-sm text-gray-600">' . h($emptyMessage) . '</p>';
    return;
}
?>

<div class="space-y-2">
    <?php foreach ($uploads as $upload): ?>
        <?= $this->Html->link(
            $this->element('atoms/file_attachment', [
                'filename' => $upload->name ?? '',
                'date' => $upload->created ? $upload->created->format('d.m.Y') : '',
                'filetype' => $upload->name ?? '',
                'removable' => false,
                'bgColor' => 'bg-white border border-gray-200'
            ]),
            ['controller' => 'Uploads', 'action' => 'view', urlencode($upload->key)],
            [
                'class' => 'block hover:opacity-80 transition-opacity',
                'escape' => false,
                'target' => '_blank'
            ]
        ) ?>
    <?php endforeach; ?>
</div>
