<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 */
?>
<div class="space-y-6 border-t pt-6">
    <!-- Candidate Section -->
    <div>
        <?= $this->element('atoms/heading', [
            'text' => __('Candidate'),
            'level' => 'h4',
            'size' => 'lg'
        ]) ?>

        <?php if($process->candidate): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mt-2">
                <div>
                    <p class="font-medium"><?= h($process->candidate->full_name) ?></p>
                    <p class="text-sm text-gray-600"><?= h($process->candidate->username) ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?= $this->element('atoms/icon', [
                        'name' => $process->candidate->enabled ? 'check-circle' : 'x-circle',
                        'size' => 'sm',
                        'options' => ['class' => $process->candidate->enabled ? 'text-green-600' : 'text-red-600']
                    ]) ?>
                    <?php if ($process->status_id === 0): ?>
                        <?= $this->element('atoms/button', [
                            'label' => __('Delete'),
                            'url' => ['action' => 'removeUser', $process->id, 'candidate'],
                            'icon' => 'trash-01',
                            'variant' => 'danger',
                            'size' => 'xs'
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-2 space-y-4">
                <?= $this->FormField->control('candidate_name', [
                    'label' => __('Candidate Name'),
                    'placeholder' => __('Candidate Name'),
                    'type' => 'text'
                ]) ?>

                <?= $this->FormField->control('candidate_email', [
                    'label' => __('Candidate Email'),
                    'placeholder' => __('Candidate Email'),
                    'type' => 'email'
                ]) ?>
                <?php
                    if ($this->Form->isFieldError('examiners')) {
                        echo $this->Form->error('examiners', __('Error: The candidate and the examiner may not be the same person.'));
                    }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Examiner Section -->
    <div>
        <?= $this->element('atoms/heading', [
            'text' => __('Examiner'),
            'level' => 'h4',
            'size' => 'lg'
        ]) ?>

        <?php if (!empty($process->examiners)): ?>
            <div class="space-y-2 mt-2">
                <?php foreach ($process->examiners as $examiner): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium"><?= h($examiner->full_name) ?></p>
                            <p class="text-sm text-gray-600"><?= h($examiner->username) ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <?= $this->element('atoms/icon', [
                                'name' => $examiner->enabled ? 'check-circle' : 'x-circle',
                                'size' => 'sm',
                                'options' => ['class' => $examiner->enabled ? 'text-green-600' : 'text-red-600']
                            ]) ?>
                            <?php if ($process->status_id === 0): ?>
                                <?= $this->element('atoms/button', [
                                    'label' => __('Delete'),
                                    'url' => [
                                        'action' => 'removeUser',
                                        $process->id,
                                        '?' => [
                                            'user_type' => 'examiner',
                                            'user_id' => $examiner->id
                                        ]
                                    ],
                                    'icon' => 'trash-01',
                                    'variant' => 'danger',
                                    'size' => 'xs'
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="mt-2 space-y-4">
                <?= $this->FormField->control('examiner_name', [
                    'label' => __('Examiner Name'),
                    'placeholder' => __('Examiner Name'),
                    'type' => 'text'
                ]) ?>

                <?= $this->FormField->control('examiner_email', [
                    'label' => __('Examiner Email'),
                    'placeholder' => __('Examiner Email'),
                    'type' => 'email'
                ]) ?>
                <?php
                    if ($this->Form->isFieldError('examiners')) {
                        echo $this->Form->error('examiners', __('Error: The candidate and the examiner may not be the same person.'));
                    }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
