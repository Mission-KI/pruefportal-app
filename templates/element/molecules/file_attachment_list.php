<?php
/**
 * File Attachment List Molecule
 *
 * Container that renders multiple file attachments.
 *
 * @var \App\View\AppView $this
 * @var array $files Array of file objects: [{name, size, type}, ...]
 * @var bool $removable Pass through to file_attachment atoms (default: false)
 * @var string|null $on_remove Alpine.js handler template (e.g., "removeFile($index)")
 */

$files = $files ?? [];
$removable = $removable ?? false;
$on_remove = $on_remove ?? null;

if (empty($files)) {
    return;
}
?>

<div class="space-y-2">
    <?php foreach ($files as $index => $file): ?>
        <?php
        $fileOnRemove = null;
        if ($removable && $on_remove) {
            // Replace $index placeholder with actual index
            $fileOnRemove = str_replace('$index', (string)$index, $on_remove);
        }
        ?>

        <?= $this->element('atoms/file_attachment', [
            'filename' => $file['name'] ?? '',
            'filesize' => $file['size'] ?? 0,
            'filetype' => $file['type'] ?? null,
            'removable' => $removable,
            'on_remove' => $fileOnRemove
        ]) ?>
    <?php endforeach; ?>
</div>
