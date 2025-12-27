<?php
/**
 * File Upload with Preview Molecule
 *
 * Combines file input button with Alpine.js state management and file preview list.
 * Reusable component for any form that needs file uploads with preview/remove functionality.
 *
 * @var \App\View\AppView $this
 * @var string $name Form field name (e.g., 'attachments' or 'indicators[0][attachments]')
 * @var string $id Unique input ID (e.g., 'files-1')
 * @var string|null $button_label Button text (default: "Dateien anhängen")
 * @var string|null $button_icon Icon name (default: "plus-square")
 * @var string|null $button_variant Button variant (default: "secondary")
 * @var string|null $button_size Button size (default: "SM")
 * @var string|null $accept Accepted file types (default: ".pdf,.doc,.docx,.png,.jpg,.jpeg")
 * @var bool $multiple Allow multiple files (default: true)
 */

$name = $name ?? 'attachments';
$id = $id ?? 'file-upload-' . uniqid();
$button_label = $button_label ?? __('Dateien anhängen');
$button_icon = $button_icon ?? 'plus-square';
$button_variant = $button_variant ?? 'secondary';
$button_size = $button_size ?? 'SM';
$accept = $accept ?? '.pdf,.doc,.docx,.png,.jpg,.jpeg';
$multiple = $multiple ?? true;
?>

<div x-data="{
    files: [],
    addFiles(event) {
        const newFiles = Array.from(event.target.files);
        for (const file of newFiles) {
            this.files.push({
                name: file.name,
                size: file.size,
                type: file.type,
                file: file
            });
        }
    },
    removeFile(index) {
        this.files.splice(index, 1);
    }
}">
    <?= $this->element('atoms/form_file_input', [
        'name' => $name,
        'id' => $id,
        'button_label' => $button_label,
        'button_icon' => $button_icon,
        'button_variant' => $button_variant,
        'button_size' => $button_size,
        'accept' => $accept,
        'multiple' => $multiple,
        'on_change' => 'addFiles($event)'
    ]) ?>

    <!-- Display selected files -->
    <div x-show="files.length > 0" class="mt-4 space-y-2">
        <template x-for="(file, index) in files" :key="index">
            <div class="bg-gray-50 rounded-lg px-4 py-2 flex items-center gap-3">
                <!-- File icon based on type -->
                <template x-if="file.type.includes('pdf')">
                    <?= $this->element('atoms/icon', [
                        'name' => 'file-icons/PDF, Type=Default',
                        'size' => 'md',
                        'options' => ['class' => 'text-gray-500']
                    ]) ?>
                </template>
                <template x-if="file.type.includes('wordprocessingml') || file.type.includes('msword')">
                    <?= $this->element('atoms/icon', [
                        'name' => 'file-icons/DOCX, Type=Default',
                        'size' => 'md',
                        'options' => ['class' => 'text-gray-500']
                    ]) ?>
                </template>
                <template x-if="file.type.includes('image')">
                    <?= $this->element('atoms/icon', [
                        'name' => 'file-icons/Image, Type=Default',
                        'size' => 'md',
                        'options' => ['class' => 'text-gray-500']
                    ]) ?>
                </template>
                <template x-if="!file.type.includes('pdf') && !file.type.includes('word') && !file.type.includes('image')">
                    <?= $this->element('atoms/icon', [
                        'name' => 'file-icons/Document, Type=Default',
                        'size' => 'md',
                        'options' => ['class' => 'text-gray-500']
                    ]) ?>
                </template>

                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 truncate" x-text="file.name"></p>
                    <p class="text-xs text-gray-500" x-text="(file.size < 1024 ? file.size + ' B' : (file.size < 1048576 ? (file.size / 1024).toFixed(1) + ' KB' : (file.size / 1048576).toFixed(1) + ' MB'))"></p>
                </div>

                <?= $this->element('atoms/button', [
                    'icon' => 'trash-01',
                    'variant' => 'ghost',
                    'size' => 'XS',
                    'click' => 'removeFile(index)',
                    'options' => ['type' => 'button']
                ]) ?>
            </div>
        </template>
    </div>
</div>
