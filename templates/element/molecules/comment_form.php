<?php
/**
 * Comment Form Molecule
 *
 * Complete form for creating new comments with subject, text, and file attachments.
 * Uses Alpine.js for client-side state management.
 *
 * @var \App\View\AppView $this
 * @var int $process_id Process ID for form submission
 * @var string|null $reference_id Pre-selected subject/reference
 * @var array|null $submit_url CakePHP URL array for form action
 * @var array|null $cancel_url Where to redirect on cancel (null = clear form only)
 */

$process_id = $process_id ?? null;
$reference_id = $reference_id ?? null;
$submit_url = $submit_url ?? ['controller' => 'Comments', 'action' => 'add', $process_id];
$cancel_url = $cancel_url ?? null;
?>

<div class="bg-white rounded-lg shadow-sm p-6"
     x-data="{
         formData: {
             reference_id: '<?= h($reference_id ?? '') ?>',
             content: '',
             files: []
         },

         addFiles(event) {
             const newFiles = Array.from(event.target.files);
             for (const file of newFiles) {
                 this.formData.files.push({
                     name: file.name,
                     size: file.size,
                     type: file.type,
                     file: file
                 });
             }
         },

         removeFile(index) {
             this.formData.files.splice(index, 1);
         },

         discardForm() {
             this.formData = {
                 reference_id: '',
                 content: '',
                 files: []
             };
             this.$refs.fileInput.value = '';
         }
     }">

    <h2 class="text-xl font-semibold text-brand-deep mb-4"><?= __('Add Comment') ?></h2>

    <div class="mb-6 p-3 bg-purple-50 border border-purple-200 rounded-lg">
        <p class="text-sm text-purple-800">
            <?= __('Saved comments are part of the assessment documentation and cannot be edited.') ?>
        </p>
    </div>

    <?= $this->Form->create(null, [
        'url' => $submit_url,
        'type' => 'file',
        'class' => 'space-y-4'
    ]) ?>

    <?= $this->Form->control('reference_id', [
        'type' => 'hidden',
        'value' => 0 // General
    ]) ?>
    <?= $this->Form->control('user_id', [
        'type' => 'hidden',
        'value' => $this->Identity->get('id')
    ]) ?>
    <?= $this->Form->control('process_id', [
        'type' => 'hidden',
        'value' => $process_id
    ]) ?>

        <?= $this->element('molecules/form_field', [
            'type' => 'textarea',
            'name' => 'content',
            'label' => __('Content'),
            'required' => true,
            'atom_element' => 'atoms/form_textarea',
            'atom_data' => [
                'id' => 'comment-text',
                'name' => 'content',
                'rows' => 6,
                'placeholder' => __('Enter your comment here...'),
                'required' => true,
                'attributes' => [
                    'class' => 'w-full',
                    'x-model' => 'formData.content'
                ]
            ]
        ]) ?>

        <div>
            <?= $this->element('atoms/form_file_input', [
                'name' => 'attachments',
                'id' => 'comment-files',
                'button_label' => __('Dateien anhängen'),
                'button_icon' => 'plus-square',
                'button_variant' => 'secondary',
                'button_size' => 'SM',
                'accept' => '.pdf,.doc,.docx,.png,.jpg,.jpeg',
                'multiple' => true,
                'on_change' => 'addFiles($event)'
            ]) ?>
        </div>

        <div x-show="formData.files.length > 0" class="space-y-2">
            <template x-for="(file, index) in formData.files" :key="index">
                <!-- Replicates atoms/file_attachment structure with Alpine.js bindings -->
                <div class="bg-gray-50 rounded-lg px-4 py-2 flex items-center gap-3">
                    <!-- Dynamic file icon based on MIME type -->
                    <template x-if="file.type.includes('pdf')">
                        <?= $this->element('atoms/icon', [
                            'name' => 'file-icons/PDF, Type=Default',
                            'size' => 'md',
                            'options' => ['class' => 'text-gray-500']
                        ]) ?>
                    </template>
                    <template x-if="file.type.includes('wordprocessingml')">
                        <?= $this->element('atoms/icon', [
                            'name' => 'file-icons/DOCX, Type=Default',
                            'size' => 'md',
                            'options' => ['class' => 'text-gray-500']
                        ]) ?>
                    </template>
                    <template x-if="file.type.includes('msword')">
                        <?= $this->element('atoms/icon', [
                            'name' => 'file-icons/DOC, Type=Default',
                            'size' => 'md',
                            'options' => ['class' => 'text-gray-500']
                        ]) ?>
                    </template>
                    <template x-if="file.type.includes('png')">
                        <?= $this->element('atoms/icon', [
                            'name' => 'file-icons/PNG, Type=Default',
                            'size' => 'md',
                            'options' => ['class' => 'text-gray-500']
                        ]) ?>
                    </template>
                    <template x-if="file.type.includes('jpeg') || file.type.includes('jpg')">
                        <?= $this->element('atoms/icon', [
                            'name' => 'file-icons/JPG, Type=Default',
                            'size' => 'md',
                            'options' => ['class' => 'text-gray-500']
                        ]) ?>
                    </template>
                    <template x-if="file.type.includes('image') && !file.type.includes('png') && !file.type.includes('jpeg') && !file.type.includes('jpg')">
                        <?= $this->element('atoms/icon', [
                            'name' => 'file-icons/Image, Type=Default',
                            'size' => 'md',
                            'options' => ['class' => 'text-gray-500']
                        ]) ?>
                    </template>
                    <template x-if="!file.type.includes('pdf') && !file.type.includes('wordprocessingml') && !file.type.includes('msword') && !file.type.includes('image')">
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
                        'options' => ['class' => 'flex-shrink-0']
                    ]) ?>
                </div>
            </template>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <?= $this->element('atoms/button', [
                'label' => __('Cancel'),
                'variant' => 'secondary',
                'size' => 'MD',
                'click' => 'discardForm()',
                'type' => 'button'
            ]) ?>

            <?= $this->element('atoms/button', [
                'label' => __('Submit'),
                'variant' => 'primary',
                'size' => 'MD',
                'type' => 'submit'
            ]) ?>
        </div>

    <?= $this->Form->end() ?>
</div>
