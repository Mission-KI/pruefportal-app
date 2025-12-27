<?php
/**
 * File Attachment Atom
 *
 * Single file display with icon, name, size/date, and optional remove button.
 *
 * @var \App\View\AppView $this
 * @var string $filename File name to display
 * @var int $filesize File size in bytes (optional if date provided)
 * @var string|null $date Date string to display instead of size (optional)
 * @var string|null $filetype MIME type or extension for icon selection
 * @var bool $removable Show remove button (default: false)
 * @var string|null $on_remove Alpine.js @click handler for remove
 * @var string|null $bgColor Background color class (default: 'bg-gray-50')
 */

$filename = $filename ?? '';
$filesize = $filesize ?? 0;
$date = $date ?? null;
$filetype = $filetype ?? null;
$removable = $removable ?? false;
$on_remove = $on_remove ?? null;
$bgColor = $bgColor ?? 'bg-gray-50';

if (empty($filename)) {
    return;
}

use App\Utility\FileIcon;

// Determine icon from filename or MIME type
$iconName = 'file-icons/Document, Type=Default';
try {
    if ($filetype && str_contains($filetype, '/')) {
        // It's a MIME type
        $icon = FileIcon::fromMimeType($filetype);
    } else {
        // It's a filename, extract icon from extension
        $icon = FileIcon::fromFilename($filename);
    }
    $iconName = 'file-icons/' . $icon->value;
} catch (\Exception $e) {
    // Fallback to document icon if anything fails
    $iconName = 'file-icons/Document, Type=Default';
}

$formattedSize = '';
if ($filesize < 1024) {
    $formattedSize = $filesize . ' B';
} elseif ($filesize < 1048576) {
    $formattedSize = number_format($filesize / 1024, 1) . ' KB';
} else {
    $formattedSize = number_format($filesize / 1048576, 1) . ' MB';
}
?>

<div class="<?= h($bgColor) ?> rounded-lg px-4 py-2 flex items-center gap-3">
    <?= $this->element('atoms/icon', [
        'name' => $iconName,
        'size' => 'md',
        'options' => ['class' => 'text-gray-500']
    ]) ?>

    <div class="flex-1 min-w-0">
        <p class="text-sm text-gray-700 truncate"><?= h($filename) ?></p>
        <p class="text-xs text-gray-600"><?= $date ? h($date) : h($formattedSize) ?></p>
    </div>

    <?php if ($removable && $on_remove): ?>
        <?= $this->element('atoms/button', [
            'icon' => 'trash-01',
            'variant' => 'tertiary',
            'size' => 'XS',
            'click' => $on_remove,
            'options' => ['class' => 'flex-shrink-0 hello-world']
        ]) ?>
    <?php endif; ?>
</div>
