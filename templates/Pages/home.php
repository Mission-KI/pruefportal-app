<?php
/**
 * @var \App\View\AppView $this
 * @var int|null $process_id Currently selected process ID
 * @var bool $hasProcesses
 */
$this->assign('title', __('Dashboard'));
$this->assign('show_content_card', 'false');
$process_id = $process_id ?? null;
?>

<?php if($this->Identity->isLoggedIn()): ?>
<div class="space-y-6">
    <?php if($hasProcesses): ?>
        <?= $this->cell('Processes::candidate', [$this->Identity->get('id')]) ?>

        <?= $this->cell('Processes::examiner', [$this->Identity->get('id')]) ?>

        <?= $this->cell('Notifications', [$this->Identity->get('id')]) ?>

        <?= $this->cell('Processes::participants', [$this->Identity->get('id'), $process_id]) ?>

        <?= $this->cell('Uploads', [$this->Identity->get('id'), $process_id]) ?>
    <?php else: ?>
        <?php /* Empty State Cell - Displayed when user has no processes */ ?>
        <?= $this->element('molecules/primary_card', [
            'title' => __('Erstes Projekt anlegen'),
            'subtitle' => __('Noch ziemlich leer hier...'),
            'body' => __('Das kann sich aber schnell ändern. Sobald der Prüfprozess gestartet ist, werden Sie an dieser Stelle jederzeit nachvollziehen können, wer gerade was macht und wo Sie im Prozess stehen. Beginnen Sie damit, indem Sie ein neues Prüfprojekt anlegen.'),
            'escape' => false
        ]) ?>

        <div class="mt-6">
            <?= $this->element('atoms/button', [
                'label' => __('Neues Projekt anlegen'),
                'variant' => 'primary',
                'icon' => 'plus',
                'url' => ['controller' => 'Projects', 'action' => 'add']
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<?php else: ?>
<h1><?= __('Welcome') ?></h1>
<p><?= __('Please login to see your dashboard') ?></p>
<?php endif; ?>
