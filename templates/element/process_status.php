<?php
/**
 * @var \App\Controller\AppController $statuses
 * @var \App\Model\Entity\Process $process
 */

 use App\Utility\Icon;

// Only use main workflow statuses (skip 0 = Inaktiv, 15 = Anwendungsfall Überprüfung, 35 = Validierungsentscheidung)
$workflowStatuses = [
    10 => $statuses[10] ?? 'Anwendungsfall',
    20 => $statuses[20] ?? 'Schutzbedarf',
    30 => $statuses[30] ?? 'VCIO-Einstufung',
    40 => $statuses[40] ?? 'Validierung',
    50 => $statuses[50] ?? 'Bewertung',
];

// Special handling for intermediate statuses:
// - Status 15 (Anwendungsfall Überprüfung): display as status 10 (Anwendungsfall)
// - Status 35 (Validierungsentscheidung): display as status 40 (Validierung)
$effectiveStatusId = $process->status_id;
if ($process->status_id === 15) {
    $effectiveStatusId = 10;
} elseif ($process->status_id === 35) {
    $effectiveStatusId = 40;
}

// Determine if current user is examiner to route to review vs view
$currentUserId = $this->request->getAttribute('identity')->id;
$isExaminer = $process->isUserExaminer($currentUserId);
$usecaseAction = ($isExaminer && $effectiveStatusId >= 15) ? 'review' : 'view';

// Get latest usecase_description ID for this process
$usecaseDescriptionId = null;
if (isset($process->usecase_descriptions) && !empty($process->usecase_descriptions)) {
    // If usecase_descriptions are already loaded, find the latest one
    $latestUcd = null;
    foreach ($process->usecase_descriptions as $ucd) {
        if ($ucd->step !== -1 && ($latestUcd === null || $ucd->version > $latestUcd->version)) {
            $latestUcd = $ucd;
        }
    }
    $usecaseDescriptionId = $latestUcd ? $latestUcd->id : null;
} else {
    // Query for latest usecase_description
    $usecaseDescriptionsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('UsecaseDescriptions');
    $latestUcd = $usecaseDescriptionsTable->find()
        ->where(['process_id' => $process->id, 'step !=' => -1])
        ->orderByDesc('version')
        ->first();
    $usecaseDescriptionId = $latestUcd ? $latestUcd->id : null;
}

// Map statuses to their index URLs
$statusUrls = [];

// Status 20 (Schutzbedarf): Only examiner can access the rating form (index)
// Status > 20: Both examiner and candidate can view results (view)
if ($process->status_id > 20) {
    $statusUrls[20] = ['controller' => 'Criteria', 'action' => 'view', $process->id];
} elseif ($process->status_id == 20 && $isExaminer) {
    $statusUrls[20] = ['controller' => 'Criteria', 'action' => 'index', $process->id];
}

// Status 30 (VCIO): Candidate works in index
// Status > 30: Both roles view results
if ($process->status_id == 30) {
    $statusUrls[30] = ['controller' => 'Indicators', 'action' => 'index', $process->id];
} elseif ($process->status_id > 30) {
    $statusUrls[30] = ['controller' => 'Indicators', 'action' => 'view', $process->id];
}

// Status 35 (Validierungsentscheidung): Candidate decides on validation
// Display as status 40 (Validierung) in the UI
if ($process->status_id == 35) {
    $statusUrls[40] = ['controller' => 'Indicators', 'action' => 'decideValidation', $process->id];
}

// Status 40 (Validation): Examiner validates in validation action
// Status >= 50: Both roles view validated results with dual classification
if ($process->status_id == 40 && $isExaminer) {
    $statusUrls[40] = ['controller' => 'Indicators', 'action' => 'validation', $process->id];
} elseif ($process->status_id >= 50) {
    $statusUrls[40] = ['controller' => 'Indicators', 'action' => 'validationView', $process->id];
}

// Status 50 (Bewertung): Show total result when process is complete (status 60)
if ($process->status_id >= 60) {
    $statusUrls[50] = ['controller' => 'Processes', 'action' => 'totalResult', $process->id];
}

// Only add UCD route if we have a valid ID
if ($usecaseDescriptionId !== null) {
    $statusUrls[10] = ['controller' => 'UsecaseDescriptions', 'action' => $usecaseAction, $usecaseDescriptionId];
}
?>


<h5 class="hidden visually-hidden"><?= __('Status') ?></h5>
<ul class="hidden sm:flex nav nav-tabs w-full gap-2 justify-center items-center">
    <?php
        // Determine which step is currently being viewed based on controller/action
        $currentController = $this->request->getParam('controller');
        $currentAction = $this->request->getParam('action');

        $viewingStatusId = null;
        if ($currentController === 'UsecaseDescriptions') {
            $viewingStatusId = 10;
        } elseif ($currentController === 'Criteria') {
            $viewingStatusId = 20;
        } elseif ($currentController === 'Indicators') {
            if (in_array($currentAction, ['index', 'add', 'complete'])) {
                $viewingStatusId = 30; // VCIO self-assessment
            } elseif ($currentAction === 'decideValidation') {
                $viewingStatusId = 40; // Validation decision (displays as Validierung)
            } elseif (in_array($currentAction, ['validation', 'validate', 'completeValidation', 'validationView'])) {
                $viewingStatusId = 40; // Validation
            } elseif ($currentAction === 'acceptValidation') {
                $viewingStatusId = 50; // Result
            } elseif ($currentAction === 'view') {
                // View always shows VCIO results (status 30)
                $viewingStatusId = 30;
            }
        } elseif ($currentController === 'Processes' && $currentAction === 'totalResult') {
            $viewingStatusId = 60;
        }

        foreach ($workflowStatuses as $key => $status):

            $url = '#';

            // Determine if this step is the one currently being viewed
            $isViewingThis = ($viewingStatusId === $key);

            $css = '';
            $icon = '';
            if($key > $effectiveStatusId) {
                $css = 'disabled text-gray-400';
                $icon = 'step-incomplete';
            } elseif($key == $effectiveStatusId) {
                $css = $isViewingThis ? 'active text-brand-light-web' : 'text-brand-deep';
                $icon = 'step-current';
            } elseif($key < $effectiveStatusId) {
                $css = $isViewingThis ? 'active text-brand-light-web' : 'completed text-brand-deep';
                $icon = 'step-complete';
            }

            // Generate URL for completed and current steps (not future steps)
            if ($key <= $effectiveStatusId && isset($statusUrls[$key])) {
                $url = $statusUrls[$key];
            }
    ?>
        <li class="nav-item max-w-1/6  flex items-center text-xs gap-2 <?= $css ?>">
            <?= $this->element('atoms/icon', ['name' => $icon]) ?>
            <?php if ($key > $effectiveStatusId): ?>
                <span class="nav-link overflow-hidden hyphens-auto break-words cursor-not-allowed"><?= $status ?></span>
            <?php elseif (is_array($url)): ?>
                <?= $this->Html->link($status, $url, ['class' => 'nav-link overflow-hidden hyphens-auto break-words']) ?>
            <?php else: ?>
                <a class="nav-link overflow-hidden hyphens-auto break-words cursor-not-allowed" href="<?= $url ?>"><?= $status ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<?php
$workflowStatusIds = array_keys($workflowStatuses);
$currentPosition = array_search($effectiveStatusId, $workflowStatusIds);

// Fallback if status not found in workflow
if ($currentPosition === false) {
    if ($effectiveStatusId > max($workflowStatusIds)) {
        $currentPosition = count($workflowStatusIds) - 1;
    } else {
        $currentPosition = -1;
    }
}

$totalStatuses = count($workflowStatusIds);
$progressPercentage = $totalStatuses > 0 ? max(0, (($currentPosition + 1) / $totalStatuses) * 100) : 0;
?>

<div class="progress my-4 bg-gray-100" role="progressbar"
     aria-label="<?= __('Process progress bar') ?>"
     aria-valuenow="<?= $currentPosition + 1 ?>"
     aria-valuemin="0"
     aria-valuemax="<?= $totalStatuses ?>"
     title="<?= __('Process progress:') ?> <?= $currentPosition + 1 ?>/<?= $totalStatuses ?>">
    <div class="progress-bar bg-brand-light-web h-2 rounded-xs" style="width: <?= round($progressPercentage) ?>%"></div>
</div>

