<?php
/**
 * Attachments Molecule
 *
 * Displays a list of file attachments for a comment or indicator.
 * Each attachment shows file icon, filename, and upload date.
 *
 * @var \App\View\AppView $this
 * @var array $uploads Array of Upload entities
 */

$uploads = $uploads ?? [];

if (empty($uploads)) {
    return;
}
?>

<div class="mt-3 space-y-2">
    <?php foreach ($uploads as $upload): ?>
    <?php if(!empty($upload)): ?>
        <?= $this->Html->link(
            $this->element('atoms/file_attachment', [
                'filename' => $upload->name ?? '',
                'date' => $upload->created ? $upload->created->format('d.m.Y') : '',
                'filetype' => $upload->name ?? '',
                'removable' => false,
                'bgColor' => 'bg-white border border-brand-light'
            ]),
            ['controller' => 'Uploads', 'action' => 'download', $upload->etag],
            [
                'class' => 'block hover:opacity-80 transition-opacity',
                'escape' => false
            ]
        ) ?>
    <?php endif; ?>
    <?php endforeach; ?>
</div>
