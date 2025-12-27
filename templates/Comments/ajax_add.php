<?php
/**
 * @var \App\View\AppView $this
 * @var integer $process_id
 * @var \App\Model\Entity\Comment $comment
 * @var \Cake\Collection\CollectionInterface|string[] $references
 */
?>
<div class="space-y-6">
    <?= $this->Form->create($comment, [
        'class' => 'space-y-6 js-ajax-form',
        'id' => 'comment-form'
    ]) ?>

    <div class="space-y-4">

        <?= $this->Form->control('reference_id', [
            'options' => [__('General')],
            'id' => 'jsReferences',
            'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand',
            'label' => ['class' => 'block text-sm font-medium text-gray-700']
        ]) ?>

        <?= $this->Form->control('content', [
            'type' => 'textarea',
            'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand',
            'label' => ['class' => 'block text-sm font-medium text-gray-700'],
            'rows' => 4,
            'required' => true,
            'placeholder' => __('Enter your comment here...')
        ]) ?>

        <?= $this->Form->control('user_id', [
            'type' => 'hidden',
            'value' => $this->Identity->get('id')
        ]) ?>

        <?= $this->Form->control('process_id', [
            'type' => 'hidden',
            'value' => $process_id
        ]) ?>
    </div>
    <div class="mt-6 flex justify-end gap-3">
        <?= $this->element('atoms/button', [
            'label' => __('Reset'),
            'variant' => 'secondary',
            'size' => 'sm',
            'type' => 'reset',
            'class' => 'px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors',
            'click' => '$dispatch("close-modal-" + $root.id)'
        ]) ?>

        <?= $this->element('atoms/button', [
            'label' => __('Submit'),
            'variant' => 'primary',
            'size' => 'sm',
            'type' => 'submit',
            'class' => 'px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-dark transition-colors'
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
