<?php
/**
 * Process Card Organism Component
 *
 * Specialized card component for displaying process information in a compact,
 * dashboard-friendly format. Matches the MEGA KI dashboard design.
 *
 * @var \App\View\AppView $this
 * @var object $process Process entity (required)
 * @var array $statuses Status configuration array (required)
 * @var array $steps Status-to-step mapping array (required)
 * @var string $user_type Type of user to display: 'examiner' or 'candidate' (deprecated - use process->acting_user)
 * @var array $actions Array of action button configurations (optional)
 * @var string $status_label Optional status label text (e.g., "In Bearbeitung")
 * @var array $options Additional HTML attributes for the card container
 */

use Cake\Core\Configure;

$process = $process ?? null;
$statuses = $statuses ?? Configure::read('statuses');
$steps = $steps ?? [0=>0, 10=>1, 15=>1, 20=>2, 30=>3, 35=>4, 40=>4, 50=>5, 60=>5];
$user_type = $user_type ?? 'candidate';
$actions = $actions ?? [];
$status_label = $status_label ?? null;
$options = $options ?? [];

if (!$process) {
    return;
}

$statusId = (int)$this->Number->format($process->status_id);
$currentStep = $steps[$statusId] ?? 0;
$statusName = $statuses[$statusId] ?? __('Unknown');
$percentage = ($currentStep / 5) * 100;
$isActive = ($statusId > 0);

// TODO: Move this logic to backend/controller
// Determine project status state based on status_id
$projectState = 'incomplete';
if ($statusId >= 60) {
    $projectState = 'complete';
} elseif ($statusId > 0) {
    $projectState = 'current';
}

// Use acting_user data prepared by ProcessesCell
$user = $process->acting_user['entity'] ?? null;
$userRole = $process->acting_user['role'] ?? '';

$cardClasses = [
    'process-card',
    $isActive ? 'bg-white' : 'bg-blue-50/25',
    'rounded-lg',
    $isActive ? 'shadow-sm' : 'shadow-md',
    'border',
    $isActive ? 'border-brand' : 'border-l-4 border-l-brand border-brand/50',
    'p-4',
    'space-y-3',
    'text-primary',
    'min-w-fit',
    'flex-grow',
    'w-full'
];

if (isset($options['class'])) {
    $cardClasses[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $cardClasses);
?>

<div<?= $this->Html->templater()->formatAttributes($options) ?>>
    <div class="flex items-center gap-2 mb-1 flex-wrap">
        <?php
        $currentUserId = $this->Identity->get('id');
        $isProjectOwner = $process->project->user_id === $currentUserId;
        ?>
            <?= $this->Html->link(
                h($process->project->title),
                ['controller' => 'Projects', 'action' => 'view', $process->project->id],
                ['class' => 'text-xs font-semibold uppercase tracking-wide flex-grow hover:underline']
            ) ?>
        <?= $this->element('atoms/project_status_icon', [
            'state' => $projectState
        ]) ?>
    </div>
    <?= $this->Html->link(
        h($process->title),
        ['controller' => 'Processes', 'action' => 'view', $process->id],
        ['class' => 'text-md font-semibold hover:underline block mb-2']
    ) ?>

    <hr class="border-gray-200 my-2">

    <div class="space-y-1 mb-2">
        <div class="text-xs">
            <?= __('Phase {0}/5', $currentStep) ?>
        </div>
        <div class="display-xs font-semibold mb-2">
            <?= h($statusName) ?>
        </div>



        <?= $this->element('process_status', ['process' => $process]); ?>

    </div>

    <div class="text-xs mb-2">
        <?= __('Letzte Aktualisierung:') ?>: <?= $process->modified->i18nFormat('dd.MM.yyyy, HH:mm') ?> <?= __('Uhr') ?>
    </div>

    <?php if ($user): ?>
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex flex-wrap gap-2">
                <?php
                $users = $process->acting_user['users'] ?? [];
                foreach ($users as $userData):
                ?>
                    <?= $this->element('molecules/user_badge', [
                        'avatar_initials' => $userData['initials'] ?? '',
                        'full_name' => h($userData['name_without_title'] ?? ''),
                        'role' => $userRole,
                        'options' => ['class' => 'text-xs']
                    ]) ?>
                <?php endforeach; ?>
            </div>
            <?php if ($status_label): ?>
                <?= $this->element('molecules/status_label', [
                    'status_id' => $statusId,
                    'label' => $status_label
                ]) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>



    <hr class="border-gray-200 my-2 md:hidden">


    <div class="flex pt-2 gap-2 md:hidden">
                <?php if ($isProjectOwner): ?>
                    <?= $this->element('atoms/button', [
                        'label' => '→ ' . __('Projektdetails'),
                        'url' => ['controller' => 'Projects', 'action' => 'view', $process->project->id],
                        'variant' => 'secondary',
                        'size' => 'xs'
                    ]) ?>
                <?php endif; ?>
                <?= $this->element('atoms/button', [
                    'label' => '→ ' . __('Prozess ansehen'),
                    'url' => ['controller' => 'Processes', 'action' => 'view', $process->id],
                    'variant' => 'secondary',
                    'size' => 'xs'
                ]) ?>
            </div>


    <?php
    // Build view links for completed steps
    $currentUserId = $this->Identity->get('id');
    $isExaminer = $process->isUserExaminer($currentUserId);
    $usecaseAction = ($isExaminer && $statusId >= 15) ? 'review' : 'view';

    // Get latest usecase_description ID
    $usecaseDescriptionId = null;
    if (isset($process->usecase_descriptions) && !empty($process->usecase_descriptions)) {
        $latestUcd = null;
        foreach ($process->usecase_descriptions as $ucd) {
            if ($ucd->step !== -1 && ($latestUcd === null || $ucd->version > $latestUcd->version)) {
                $latestUcd = $ucd;
            }
        }
        $usecaseDescriptionId = $latestUcd ? $latestUcd->id : null;
    }

    $viewLinks = [];

    // Only add view links for completed steps
    if ($usecaseDescriptionId !== null && $statusId > 10) {
        $viewLinks[] = [
            'label' => __('Anwendungsfallbeschreibung'),
            'url' => ['controller' => 'UsecaseDescriptions', 'action' => $usecaseAction, $usecaseDescriptionId]
        ];
    }

    if ($statusId > 20) {
        $viewLinks[] = [
            'label' => __('Schutzbedarf'),
            'url' => ['controller' => 'Criteria', 'action' => 'view', $process->id]
        ];
    }

    if ($statusId > 30) {
        $viewLinks[] = [
            'label' => __('VCIO-Einstufung'),
            'url' => ['controller' => 'Indicators', 'action' => 'view', $process->id]
        ];
    }

    if ($statusId >= 40) {
        $viewLinks[] = [
            'label' => __('Validierung'),
            'url' => ['controller' => 'Indicators', 'action' => 'view', $process->id]
        ];
    }
    ?>

    <?php if (!empty($viewLinks) || !empty($actions)): ?>
        <hr class="border-gray-200 my-2">

        <!-- Actions and View Links -->
        <div class="flex flex-wrap items-center gap-2 pt-2">
            <!-- Primary Action Button -->
            <?php if (!empty($actions)): ?>
                <?php foreach ($actions as $action): ?>
                    <?= $this->element('atoms/button', array_merge([
                        'variant' => 'primary',
                        'size' => 'sm'
                    ], $action)) ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- View Links -->
            <?php if (!empty($viewLinks)): ?>
                <div class="flex flex-wrap items-center gap-2 ml-auto">
                    <?php foreach ($viewLinks as $link): ?>
                        <?= $this->Html->link(
                            $this->element('atoms/icon', ['name' => 'arrow-right']) . ' ' . $link['label'],
                            $link['url'],
                            ['class' => 'text-xs flex items-center gap-1 hover:underline', 'escape' => false]
                        ) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
