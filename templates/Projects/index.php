<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Project> $projects
 * @var \App\Controller\AppController $statuses
 */
$this->assign('title', __('Projekte'));
?>

<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => __('Projekte'),
    'size' => false,
    'weight' => false,
    'options' => ['class' => 'text-brand display-xs mb-6']
]) ?>

<?= $this->element('molecules/primary_card', [
    'title' => __('Projektübersicht'),
    'subtitle' => __('Projekte'),
    'body' => __('Wählen Sie ein Projekt aus, um es zu verwalten, die jeweiligen Prüfprozesse zu sehen und deren Status abzurufen'),
]) ?>

<div class="space-y-3 mb-6">
    <?php foreach ($projects as $project): ?>
    <div>
        <?= $this->element('atoms/project_link', [
            'text' => h($project->title),
            'url' => ['action' => 'view', $project->id]
        ]) ?>
    </div>
    <?php endforeach; ?>
</div>

<?= $this->element('molecules/pagination') ?>

<div class="flex justify-end mt-6">
    <?= $this->element('atoms/button', [
        'label' => __('Neues Projekt anlegen'),
        'url' => ['action' => 'add'],
        'variant' => 'primary',
        'size' => 'sm',
        'icon' => 'plus'
    ]) ?>
</div>
