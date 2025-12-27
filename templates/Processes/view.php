<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 */
$this->assign('title', h($process->title));
$this->assign('show_right_sidebar', 'false');
$this->assign('reserve_sidebar_space', 'false');

// Prepare role options for select (process participant roles)
$roleOptions = [
    '' => __('Bitte wählen'),
    'examiner' => __('Prüfer/in'),
    'candidate' => __('Prüfling')
];

// Build participants list (examiners and candidate)
$participants = [];
$participantIds = [];

if (!empty($process->examiners)) {
    foreach ($process->examiners as $examiner) {
        $participantIds[] = $examiner->id;
        $participants[] = $examiner;
    }
}
if ($process->hasValue('candidate')) {
    $participantIds[] = $process->candidate->id;
    $participants[] = $process->candidate;
}

// Participant table columns
$participantColumns = [
    [
        'field' => 'name',
        'label' => __('Name'),
        'sortable' => false,
        'renderer' => function($user, $view) {
            $nameParts = explode(' ', $user->full_name);
            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
            return $view->element('molecules/user_badge', [
                'avatar_initials' => $initials,
                'full_name' => $user->salutation_name
            ]);
        }
    ],
    [
        'field' => 'created',
        'label' => __('Eingeladen am'),
        'sortable' => false,
        'renderer' => function($user, $view) {
            return $view->element('atoms/timestamp', [
                'datetime' => $user->created,
                'format' => 'd.m.Y'
            ]);
        }
    ],
    [
        'field' => 'enabled',
        'label' => __('Status'),
        'sortable' => false,
        'renderer' => function($user, $view) {
            return $view->element('molecules/status_label', [
                'status_id' => $user->enabled ? 1 : 0,
                'label' => $user->enabled ? __('Aktiv') : __('Inaktiv')
            ]);
        }
    ],
    [
        'field' => 'role',
        'label' => __('Rolle'),
        'sortable' => false,
        'renderer' => function($user, $view) use ($process) {
            if ($process->isUserExaminer($user->id)) {
                return __('Prüfer/in');
            } elseif ($process->candidate_user === $user->id) {
                return __('Prüfling');
            }
            return '';
        }
    ],
    [
        'field' => 'username',
        'label' => __('E-Mail Adresse'),
        'sortable' => false
    ]
];

$participantActionRenderer = function($user, $view) use ($process) {
    // Determine user role in process
    $role = null;
    if ($process->isUserExaminer($user->id)) {
        $role = 'examiner';
    } elseif ($process->candidate_user === $user->id) {
        $role = 'candidate';
    }

    return $view->element('molecules/table_actions', [
        'actions' => [
            [
                'icon' => 'edit',
                'url' => ['controller' => 'Users', 'action' => 'edit', $user->id],
                'label' => __('Edit')
            ],
            [
                'icon' => 'trash-01',
                'url' => ['controller' => 'Processes', 'action' => 'removeUser', $process->id, $role, '?' => ['user_id' => $user->id]],
                'label' => __('Delete'),
                'method' => 'post',
                'confirm' => __('Are you sure?')
            ]
        ]
    ]);
};
?>

<?php
use App\Utility\StringHelper;

// Build action buttons using ProcessHelper
$actionButtons = [];

// Determine user type (candidate or examiner)
$userId = $this->Identity->get('id');
$userType = $this->Process->determineUserType($process->status_id, $userId, $process);

// Get continue action from helper
$continueAction = $this->Process->getContinueAction($process, $userType);
if ($continueAction) {
    $actionButtons[] = $this->element('atoms/button', [
        'label' => $continueAction['label'],
        'url' => $continueAction['url'],
        'variant' => $continueAction['variant'],
        'size' => $continueAction['size'],
        'options' => ['class' => 'w-full md:w-auto']
    ]);
}

// Determine acting user (who needs to act next)
$actingUserType = match ((int)$process->status_id) {
    10, 20, 30, 35, 50 => 'candidate',
    40 => 'examiner',
    60 => 'complete',
    default => 'candidate'
};

$actingUsers = [];
$actingUserRole = '';
if ($actingUserType === 'examiner' && !empty($process->examiners)) {
    foreach ($process->examiners as $examiner) {
        $nameWithoutTitle = StringHelper::removeTitle($examiner->salutation_name ?? '');
        $actingUsers[] = [
            'entity' => $examiner,
            'name_without_title' => $nameWithoutTitle,
            'initials' => StringHelper::getInitials($nameWithoutTitle),
        ];
    }
    $actingUserRole = __('Prüfer/in');
} elseif ($actingUserType === 'candidate' && $process->hasValue('candidate')) {
    $nameWithoutTitle = StringHelper::removeTitle($process->candidate->salutation_name ?? '');
    $actingUsers[] = [
        'entity' => $process->candidate,
        'name_without_title' => $nameWithoutTitle,
        'initials' => StringHelper::getInitials($nameWithoutTitle),
    ];
    $actingUserRole = __('Prüfling');
}

// Status label for acting user section
$actingStatusLabel = match ((int)$process->status_id) {
    40 => __('Wartet auf Prüfer'),
    60 => __('Fertig'),
    default => __('In Bearbeitung')
};

// Get status configuration
$statuses = \Cake\Core\Configure::read('statuses');
$steps = [0=>0, 10=>1, 15=>1, 20=>2, 30=>3, 35=>4, 40=>4, 50=>5, 60=>5];
$statusId = (int)$process->status_id;
$currentStep = $steps[$statusId] ?? 0;
$statusName = $statuses[$statusId] ?? __('Unknown');
$isActive = ($statusId > 0);

$statusWidgetClasses = [
    'mb-6',
    'rounded-lg',
    'p-6',
    $isActive ? 'bg-white' : 'bg-blue-50/25',
    $isActive ? 'shadow-sm' : 'shadow-md',
    'border',
    $isActive ? 'border-gray-200' : 'border-l-4 border-l-brand border-brand/50',
    'text-primary'
];
?>

<!-- Process Header -->
<div class="mb-6">
    <!-- Project title (suptitle) -->
    <div class="text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">
        <?= $this->Html->link(
            h($process->project->title),
            ['controller' => 'Projects', 'action' => 'view', $process->project->id],
            ['class' => 'hover:underline']
        ) ?>
    </div>

    <!-- Process title -->
    <?= $this->element('atoms/heading', [
        'level' => 1,
        'text' => h($process->title),
        'size' => false,
        'weight' => false,
        'options' => ['class' => 'text-brand display-xs mb-6']
    ]) ?>
</div>

<!-- Status Widget -->
<div class="<?= implode(' ', $statusWidgetClasses) ?>">
    <!-- Phase info -->
    <div class="mb-4">
        <div class="text-xs mb-1">
            <?= __('Phase {0}/5', $currentStep) ?>
        </div>
        <div class="display-xs font-semibold mb-3">
            <?= h($statusName) ?>
        </div>

        <!-- Process status -->
        <?= $this->element('process_status', ['process' => $process]); ?>
    </div>

    <!-- Last update -->
    <div class="text-xs mb-4">
        <?= __('Letzte Aktualisierung:') ?> <?= $process->modified->i18nFormat('dd.MM.yyyy, HH:mm') ?> <?= __('Uhr') ?>
    </div>

    <?php if (!empty($actingUsers)): ?>
        <!-- Acting user -->
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($actingUsers as $userData): ?>
                    <?= $this->element('molecules/user_badge', [
                        'avatar_initials' => $userData['initials'] ?? '',
                        'full_name' => h($userData['name_without_title'] ?? ''),
                        'role' => $actingUserRole,
                        'options' => ['class' => 'text-xs']
                    ]) ?>
                <?php endforeach; ?>
            </div>
            <?= $this->element('molecules/status_label', [
                'status_id' => $statusId,
                'label' => $actingStatusLabel
            ]) ?>
        </div>
    <?php endif; ?>

    <?php
    // Build view links for completed steps (same logic as process_status)
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

    <?php if (!empty($viewLinks) || !empty($actionButtons)): ?>
        <!-- Divider -->
        <hr class="border-gray-200 my-4">

        <!-- Actions and View Links -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Primary Action Button -->
            <?php if (!empty($actionButtons)): ?>
                <?php foreach ($actionButtons as $button): ?>
                    <?= $button ?>
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

<div class="mb-8 border border-gray-200 rounded-lg p-6" x-data="{ editMode: false }">
    <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-4">
        <?= $this->element('atoms/icon', ['name' => 'info-circle', 'size' => 'md']) ?>
        <?= __('Übersicht') ?>
    </h3>

    <?= $this->Form->create($process, ['url' => ['action' => 'edit', $process->id]]) ?>

    <!-- Read-only view -->
    <div x-show="!editMode" class="space-y-4 mb-4">
        <?= $this->element('molecules/form_field_readonly', [
            'label' => __('Name des Prüfprozesses'),
            'value' => $process->title
        ]) ?>

        <?= $this->element('molecules/form_field_readonly', [
            'label' => __('Beschreibung des Prüfprozesses'),
            'value' => $process->description
        ]) ?>
    </div>

    <!-- Edit mode -->
    <div x-show="editMode" class="space-y-4 mb-4">
        <?= $this->element('molecules/form_field', [
            'name' => 'title',
            'label' => __('Name des Prüfprozesses'),
            'type' => 'text',
            'atom_element' => 'atoms/form_input',
            'atom_data' => [
                'name' => 'title',
                'value' => $process->title,
                'id' => 'process-title'
            ]
        ]) ?>

        <?= $this->element('molecules/form_field', [
            'name' => 'description',
            'label' => __('Beschreibung des Prüfprozesses'),
            'type' => 'textarea',
            'atom_element' => 'atoms/form_textarea',
            'atom_data' => [
                'name' => 'description',
                'value' => $process->description,
                'id' => 'process-description',
                'rows' => 4
            ]
        ]) ?>
    </div>

    <!-- Action buttons -->
    <div class="flex gap-2 justify-end">
        <div x-show="!editMode">
            <?= $this->element('atoms/button', [
                'label' => __('Bearbeiten'),
                'type' => 'button',
                'variant' => 'secondary',
                'size' => 'sm',
                'icon' => 'edit',
                'options' => ['@click' => 'editMode = true']
            ]) ?>
        </div>
        <div x-show="editMode" class="flex gap-2">
            <?= $this->element('atoms/button', [
                'label' => __('Abbrechen'),
                'type' => 'button',
                'variant' => 'secondary',
                'size' => 'sm',
                'options' => ['@click' => 'editMode = false']
            ]) ?>
            <?= $this->element('atoms/button', [
                'label' => __('Speichern'),
                'type' => 'submit',
                'variant' => 'primary',
                'size' => 'sm'
            ]) ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>

<div class="mb-8 border border-gray-200 rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="flex items-center gap-2 text-lg font-regular text-brand">
            <?= $this->element('atoms/icon', ['name' => 'user-group', 'size' => 'md']) ?>
            <?= __('Prozessbeteiligte') ?>
            <span class="text-sm font-normal text-gray-500">
                <?= count(array_filter($participants, fn($p) => $p->enabled ?? false)) ?> von <?= count($participants) ?> aktiv
            </span>
        </h3>
    </div>

    <?php if (!empty($participants)): ?>
        <?= $this->element('organisms/sortable_table', [
            'data' => $participants,
            'columns' => $participantColumns,
            'features' => [
                'sortable' => false,
                'selectable' => false,
                'actions' => true
            ],
            'actionRenderer' => $participantActionRenderer,
            'emptyState' => [
                'icon' => 'user-group',
                'title' => __('Keine Prozessbeteiligten'),
                'message' => __('Keine Prozessbeteiligten vorhanden.')
            ]
        ]) ?>
    <?php else: ?>
        <p class="text-gray-500 mb-4"><?= __('Keine Prozessbeteiligten vorhanden.') ?></p>
    <?php endif; ?>
</div>

<div class="mb-8 border border-gray-200 rounded-lg p-6">
    <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-4">
        <?= $this->element('atoms/icon', ['name' => 'user-add', 'size' => 'md']) ?>
        <?= __('Neue Prozessbeteiligte hinzufügen') ?>
    </h3>

    <?= $this->Form->create(null, [
          'url' => ['controller' => 'Processes', 'action' => 'addParticipant', $process->id],
          'x-data' => '{ formValid: false }',
          'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
          '@input' => 'formValid = $el.checkValidity()'
      ]) ?>
      <div class="flex gap-4 mb-4 flex-wrap">
          <?= $this->element('molecules/form_field', [
              'name' => 'name',
              'label' => __('Name'),
              'type' => 'text',
              'required' => true,
              'atom_element' => 'atoms/form_input',
              'atom_data' => [
                  'name' => 'name',
                  'id' => 'participant-name',
                  'placeholder' => '',
                  'required' => true
              ],
                             'containerClass' => 'lg:w-1/4',
                             'controlClass' => 'lg:min-w-full'
          ]) ?>

          <?= $this->element('molecules/form_field', [
              'name' => 'role',
              'label' => __('Rolle'),
              'type' => 'select',
              'required' => true,
              'atom_element' => 'atoms/form_select',
              'atom_data' => [
                  'name' => 'role',
                  'id' => 'participant-role',
                  'options' => $roleOptions,
                  'value' => '',
                  'required' => true
              ],
                             'containerClass' => 'lg:w-1/4',
                             'controlClass' => 'lg:min-w-full'
          ]) ?>

          <?= $this->element('molecules/form_field', [
              'name' => 'email',
              'label' => __('E-Mail'),
              'type' => 'email',
              'required' => true,
              'atom_element' => 'atoms/form_input',
              'icon' => 'mail',
              'atom_data' => [
                  'name' => 'email',
                  'id' => 'participant-email',
                  'type' => 'email',
                  'required' => true
              ],
              'client_error_messages' => [
                  __('Bitte geben Sie eine gültige E-Mail-Adresse ein.')
              ],
                             'containerClass' => 'lg:w-1/4',
                             'controlClass' => 'lg:min-w-full'
          ]) ?>

          <div class="mt-[calc(1.5rem+0.5rem)]">
              <?= $this->element('atoms/button', [
                  'label' => __('Einladen'),
                  'type' => 'submit',
                  'variant' => 'primary',
                  'size' => 'md',
                  'icon' => 'mail',
                  'options' => [
                      ':disabled' => '!formValid'
                  ]
              ]) ?>
          </div>
      </div>
      <?= $this->Form->end() ?>
</div>

<?php if ($process->project->user_id === $this->Identity->get('id')): ?>
<div class="mb-8 border border-error-200 rounded-lg p-6 bg-error-50">
    <?= $this->element('atoms/alert', [
        'type' => 'error',
        'title' => __('Prüfprozess löschen'),
        'icon' => 'alert-triangle',
        'size' => 'text-sm',
        'message' => __('Achtung: Nach dem Löschen Ihres Prüfprozesses können die Daten nicht wiederhergestellt werden. Diese Aktion ist permanent und kann nicht rückgängig gemacht werden.'),
        'dismissible' => false
    ]) ?>

    <div class="mt-4">
        <?= $this->Form->postLink(
            __('Prüfprozess löschen'),
            ['action' => 'delete', $process->id],
            [
                'confirm' => __('Sind Sie sicher, dass Sie diesen Prüfprozess löschen möchten?'),
                'class' => 'btn btn-error btn-sm'
            ]
        ) ?>
    </div>
</div>
<?php endif; ?>

