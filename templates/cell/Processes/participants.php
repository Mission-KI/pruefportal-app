<?php
/**
 * @var App\View\Cell\ProcessesCell $processes
 * @var int|null $process_id Currently selected process ID
 */
?>

<?php if (!empty($processes) || !empty($participants)): ?>
    <?php
    // Prepare main content
    ob_start();
    ?>
    <?php if (!empty($participants)): ?>
        <div class="mt-4 space-y-1">
            <?php if ($participants->hasValue('project')): ?>
                <?= $this->element('molecules/participant_list_item', [
                    'initials' => $this->Layout->getInitials($participants['project']['user']['full_name']),
                    'full_name' => $participants['project']['user']['full_name'],
                    'role' => __('Owner')
                ]) ?>
            <?php endif; ?>

            <?php if (!empty($participants['examiners'])): ?>
                <?php foreach ($participants['examiners'] as $examiner): ?>
                    <?= $this->element('molecules/participant_list_item', [
                        'initials' => $this->Layout->getInitials($examiner['full_name']),
                        'full_name' => $examiner['full_name'],
                        'role' => __('Examiner')
                    ]) ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($participants->hasValue('candidate')): ?>
                <?= $this->element('molecules/participant_list_item', [
                    'initials' => $this->Layout->getInitials($participants['candidate']['full_name']),
                    'full_name' => $participants['candidate']['full_name'],
                    'role' => __('Candidate')
                ]) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php
    $widgetContent = ob_get_clean();

    // Prepare footer content (only for project owner)
    $footerContent = null;
    if (!empty($participants) && $participants['project']['user']['id'] === $this->Identity->get('id') && $participants['status_id'] === 0) {
        ob_start();
        ?>
        <div class="flex justify-end">
            <?= $this->element('atoms/button', [
                'label' => __('Nutzer verwalten'),
                'variant' => 'primary',
                'icon' => 'user-edit',
                'url' => ['controller' => 'Processes', 'action' => 'edit', $process_id]
            ]) ?>
        </div>
        <?php
        $footerContent = ob_get_clean();
    }

    // Render dashboard widget
    echo $this->element('organisms/dashboard_widget', [
        'icon' => 'user-group',
        'title' => __('Prozessbeteiligte'),
        'processes' => !empty($processes) ? $processes : null,
        'process_id' => $process_id,
        'filter_redirect' => 'participants',
        'content' => $widgetContent,
        'footer' => $footerContent
    ]);
    ?>
<?php endif; ?>
