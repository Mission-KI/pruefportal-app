<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Project $project
 */
$this->assign('title', h($project->title));

$isOwner = $project->user_id === $this->Identity->get('id');

// Prepare process options for select
$processOptions = ['' => __('Bitte wählen')];
if ($project->has('processes')) {
    foreach ($project->processes as $process) {
        $processOptions[$process->id] = $process->title;
    }
}

// Prepare role options for select (process participant roles)
$roleOptions = [
    '' => __('Bitte wählen'),
    'examiner' => __('Prüfer/in'),
    'candidate' => __('Prüfling')
];

// Process table columns with custom renderers
$processColumns = [
    [
        'field' => 'title',
        'label' => __('Name'),
        'sortable' => false
    ],
    [
        'field' => 'examiners',
        'label' => __('Prüfer/in'),
        'sortable' => false,
        'renderer' => function($process, $view) {
            if (!empty($process->examiners)) {
                $badges = [];
                foreach ($process->examiners as $examiner) {
                    $nameParts = explode(' ', $examiner->full_name);
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    $badges[] = $view->element('molecules/user_badge', [
                        'avatar_initials' => $initials,
                        'full_name' => $examiner->salutation_name
                    ]);
                }
                return '<div class="flex flex-wrap gap-2">' . implode('', $badges) . '</div>';
            }
            return '';
        }
    ],
    [
        'field' => 'candidate',
        'label' => __('Prüfling'),
        'sortable' => false,
        'renderer' => function($process, $view) {
            if ($process->hasValue('candidate')) {
                $nameParts = explode(' ', $process->candidate->full_name);
                $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                return $view->element('molecules/user_badge', [
                    'avatar_initials' => $initials,
                    'full_name' => $process->candidate->salutation_name
                ]);
            }
            return '';
        }
    ],
    [
        'field' => 'created',
        'label' => __('Startdatum'),
        'sortable' => false,
        'renderer' => function($process, $view) {
            return $view->element('atoms/timestamp', [
                'datetime' => $process->created,
                'format' => 'd.m.Y'
            ]);
        }
    ],
    [
        'field' => 'status_id',
        'label' => __('Status'),
        'sortable' => false,
        'renderer' => function($process, $view) {
            return $view->element('molecules/status_label', [
                'status_id' => $process->status_id,
                'label' => __('in Bearbeitung')
            ]);
        }
    ]
];

$processActionRenderer = function($process, $view) use ($isOwner) {
    $actions = [
        [
            'icon' => 'edit',
            'url' => ['controller' => 'Processes', 'action' => 'view', $process->id],
            'label' => __('View')
        ]
    ];

    if ($isOwner) {
        $actions[] = [
            'icon' => 'trash-01',
            'url' => ['controller' => 'Processes', 'action' => 'delete', $process->id],
            'label' => __('Delete'),
            'method' => 'delete',
            'confirm' => __('Are you sure?')
        ];
    }

    return $view->element('molecules/table_actions', [
        'actions' => $actions
    ]);
};

// Build participants table data
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
        'field' => 'processes',
        'label' => __('Prozess'),
        'sortable' => false,
        'renderer' => function($user, $view) {
            return h($user->process_title ?? '');
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
        'renderer' => function($user, $view) {
            if (isset($user->participant_role)) {
                return $user->participant_role === 'examiner' ? __('Prüfer/in') : __('Prüfling');
            }
            return h($user->role->name ?? '');
        }
    ],
    [
        'field' => 'username',
        'label' => __('E-Mail Adresse'),
        'sortable' => false
    ]
];

$participantActionRenderer = function($user, $view) {
    return $view->element('molecules/table_actions', [
        'actions' => [
            [
                'icon' => 'edit',
                'url' => ['controller' => 'Users', 'action' => 'edit', $user->id],
                'label' => __('Edit')
            ],
            [
                'icon' => 'trash-01',
                'url' => ['controller' => 'Processes', 'action' => 'removeUser', $user->process_id, $user->participant_role, '?' => ['user_id' => $user->id]],
                'label' => __('Delete'),
                'method' => 'post',
                'confirm' => __('Are you sure?')
            ]
        ]
    ]);
};

// Gather participants with process and role information
$participantRows = [];

if ($project->has('processes')) {
    foreach ($project->processes as $process) {
        if (!empty($process->examiners)) {
            foreach ($process->examiners as $examiner) {
                $row = clone $examiner;
                $row->process_id = $process->id;
                $row->process_title = $process->title;
                $row->participant_role = 'examiner';
                $participantRows[] = $row;
            }
        }
        if ($process->hasValue('candidate')) {
            $row = clone $process->candidate;
            $row->process_id = $process->id;
            $row->process_title = $process->title;
            $row->participant_role = 'candidate';
            $participantRows[] = $row;
        }
    }
}
?>

<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => h($project->title),
    'size' => false,
    'weight' => false,
    'options' => ['class' => 'text-brand display-xs mb-6']
]) ?>

<div class="mb-8 border border-gray-200 rounded-lg p-6" x-data="{ editMode: false }">
    <h3 class="flex items-center gap-2 text-lg font-regular text-brand mb-4">
        <?= $this->element('atoms/icon', ['name' => 'info-circle', 'size' => 'md']) ?>
        <?= __('Übersicht') ?>
    </h3>

    <?= $this->Form->create($project, ['url' => ['action' => 'edit', $project->id]]) ?>

    <!-- Read-only view -->
    <div x-show="!editMode" class="grid grid-cols-1 mb-4 md:grid-cols-3 gap-4">
        <?= $this->element('molecules/form_field_readonly', [
            'label' => __('Projekt-Name'),
            'value' => $project->title
        ]) ?>

        <?= $this->element('molecules/form_field_readonly', [
            'label' => __('Projekt-Owner'),
            'value' => $project->user->salutation_name ?? ''
        ]) ?>

        <?= $this->element('molecules/form_field_readonly', [
            'label' => __('Startdatum'),
            'value' => $project->created->format('d.m.Y')
        ]) ?>
    </div>

    <!-- Edit mode -->
    <div x-show="editMode" class="grid grid-cols-1 mb-4 md:grid-cols-3 gap-4">
        <?= $this->element('molecules/form_field', [
            'name' => 'title',
            'label' => __('Projekt-Name'),
            'type' => 'text',
            'atom_element' => 'atoms/form_input',
            'atom_data' => [
                'name' => 'title',
                'value' => $project->title,
                'id' => 'project-title'
            ]
        ]) ?>

        <?= $this->element('molecules/form_field', [
            'name' => 'owner',
            'label' => __('Projekt-Owner'),
            'type' => 'text',
            'atom_element' => 'atoms/form_input',
            'atom_data' => [
                'name' => 'owner',
                'value' => $project->user->salutation_name ?? '',
                'id' => 'project-owner'
            ]
        ]) ?>

        <?= $this->element('molecules/form_field', [
            'name' => 'start_date',
            'label' => __('Startdatum'),
            'type' => 'text',
            'atom_element' => 'atoms/form_input',
            'atom_data' => [
                'name' => 'start_date',
                'value' => $project->created->format('d.m.Y'),
                'id' => 'project-start-date'
            ]
        ]) ?>
    </div>


<?php if ($isOwner): ?>
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
<?php endif; ?>

    <?= $this->Form->end() ?>
    <h4 class="text-md font-regular text-brand mb-4"><?= __('Zugehörige Prüfprozesse') ?></h4>

    <?php if ($project->has('processes') && count($project->processes) > 0): ?>
        <?= $this->element('organisms/sortable_table', [
            'data' => $project->processes,
            'columns' => $processColumns,
            'features' => [
                'sortable' => false,
                'selectable' => false,
                'actions' => $isOwner
            ],
            'actionRenderer' => $processActionRenderer,
            'emptyState' => [
                'icon' => 'file-save',
                'title' => __('Keine Prüfprozesse'),
                'message' => __('Keine Prüfprozesse vorhanden.')
            ]
        ]) ?>


        <?php if ($isOwner): ?>
            <div class="flex justify-end mt-4">

                <?= $this->element('atoms/button', [
                    'label' => __('Neuen Prüfprozess anlegen'),
                    'url' => ['controller' => 'Processes', 'action' => 'add', $project->id],
                    'variant' => 'primary',
                    'size' => 'sm',
                    'icon' => 'plus'
                ]) ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-gray-500 mb-4"><?= __('Keine Prüfprozesse vorhanden.') ?></p>
        <?= $this->element('atoms/button', [
            'label' => __('Neuen Prüfprozess anlegen'),
            'url' => ['controller' => 'Processes', 'action' => 'add', $project->id],
            'variant' => 'primary',
            'size' => 'sm',
            'icon' => 'plus'
        ]) ?>
    <?php endif; ?>
</div>

<div class="mb-8 border border-gray-200 rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="flex items-center gap-2 text-lg font-regular text-brand">
            <?= $this->element('atoms/icon', ['name' => 'user-group', 'size' => 'md']) ?>
            <?= __('Projektbeteiligte') ?>
            <span class="text-sm font-normal text-gray-500"><?= __('3 von 5 aktiv') ?></span>
        </h3>
    </div>

    <div class="mb-4">
        <?= $this->Form->select('process_filter',
            [__('Alle Prüfprozesse')],
            ['class' => 'form-select']
        ) ?>
    </div>

    <?php if (!empty($participantRows)): ?>
        <?= $this->element('organisms/sortable_table', [
            'data' => $participantRows,
            'columns' => $participantColumns,
            'features' => [
                'sortable' => false,
                'selectable' => false,
                'actions' => $isOwner
            ],
            'actionRenderer' => $participantActionRenderer,
            'emptyState' => [
                'icon' => 'user-group',
                'title' => __('Keine Projektbeteiligten'),
                'message' => __('Keine Projektbeteiligten vorhanden.')
            ]
        ]) ?>
    <?php else: ?>
        <p class="text-gray-500 mb-4"><?= __('Keine Projektbeteiligten vorhanden.') ?></p>
    <?php endif; ?>
</div>

<?php if ($isOwner): ?>
<div class="mb-8 border border-gray-200 rounded-lg p-6">
    <h3 class="flex items-center-safe gap-2 text-lg font-regular text-brand mb-4">
        <?= $this->element('atoms/icon', ['name' => 'user-add', 'size' => 'md']) ?>
        <?= __('Neue Projektbeteiligte hinzufügen') ?>
    </h3>

    <?= $this->Form->create(null, [
          'url' => ['controller' => 'Processes', 'action' => 'addParticipant'],
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
              'containerClass' => 'lg:w-1/5',
              'controlClass' => 'lg:min-w-full'
          ]) ?>

          <?= $this->element('molecules/form_field', [
              'name' => 'process',
              'label' => __('Prüfprozess'),
              'type' => 'select',
              'required' => true,
              'atom_element' => 'atoms/form_select',
              'atom_data' => [
                  'name' => 'process',
                  'id' => 'participant-process',
                  'options' => $processOptions,
                  'value' => '',
                  'required' => true
              ],
              'containerClass' => 'lg:w-1/5',
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
              'containerClass' => 'lg:w-1/5',
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
              'containerClass' => 'lg:w-1/5',
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
<?php endif; ?>

<?php if ($isOwner): ?>
<div class="mb-8 border border-error-200 rounded-lg p-6 bg-error-50">
    <?= $this->element('atoms/alert', [
        'type' => 'error',
        'title' => __('Projekt löschen'),
        'icon' => 'alert-triangle',
        'size' => 'text-sm',
        'message' => __('Achtung: Nach dem Löschen Ihres Projektes können die Daten nicht wiederhergestellt werden. Das Löschen eines Projektes hat auch die Löschung aller damit verbundenen Prüfprozesse zufolge. Diese Aktion ist permanent und kann nicht rückgängig gemacht werden.'),
        'dismissible' => false
    ]) ?>

    <div class="mt-4">
        <?= $this->Form->postLink(
            __('Projekt löschen'),
            ['action' => 'delete', $project->id],
            [
                'confirm' => __('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?'),
                'class' => 'btn btn-error btn-sm'
            ]
        ) ?>
    </div>
</div>
<?php endif; ?>
