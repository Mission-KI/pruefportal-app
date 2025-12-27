<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Project $project
 */
$this->assign('title', __('New Project'));
$this->assign('reserve_sidebar_space', 'true');
?>

<!-- Page Heading -->
<?= $this->element('atoms/heading', [
    'level' => 1,
    'text' => __('Projects'),
    'options' => ['class' => 'display-sm text-brand-deep mb-6']
]) ?>

<!-- Purple Info Banner -->
<?= $this->element('molecules/primary_card', [
    'title' => __('Create New Project'),
    'subtitle' => __('Project'),
    'body' => __('The foundation of an assessment process is the project, which it is assigned to. A project can be understood as a "roof" for multiple assessment processes. Example: The project "Company-AI" could include the assessment processes "HR Tool", "Process Automation", and "Production Assistant".'),
    'escape' => false,
]) ?>

<!-- Section Heading -->
<h3 class="display-xs text-brand-deep mb-6 mt-8">
    <?= __('General Information') ?>
</h3>

<!-- Form -->
<?= $this->Form->create($project, [
    'class' => 'space-y-6',
    'id' => 'project-form',
    'novalidate' => true,
    'x-data' => '{ formValid: false }',
    'x-init' => '$watch("formValid", () => { formValid = $el.checkValidity() })',
    '@input' => 'formValid = $el.checkValidity()'
]) ?>

    <?= $this->FormField->control('title', [
        'label' => __('Project Name'),
        'type' => 'text',
        'required' => true,
        'tooltip' => __('Choose a descriptive name that clearly identifies the project. This will be the umbrella under which all assessment processes are organized.'),
        'help' => __('A project can contain multiple separate assessment processes.'),
        'error_messages' => [__('Please enter a project name.')],
        'placeholder' => __('e.g., Organization, Testing Area, etc.'),
        'class' => 'w-full'
    ]) ?>

    <?= $this->FormField->control('description', [
        'label' => __('Project Description'),
        'type' => 'textarea',
        'required' => true,
        'help' => __('Choose a clear name that helps assessment participants orient themselves. You can edit the Project Description later.'),
        'error_messages' => [__('Please enter a Project Description.')],
        'placeholder' => __('e.g., Image Recognition'),
        'class' => 'w-full'
    ]) ?>

    <?= $this->element('molecules/form_field', [
        'name' => 'process_title',
        'label' => __('Process Name'),
        'type' => 'text',
        'required' => true,
        'tooltip' => __('Choose a clear name that helps assessment participants orient themselves. You can add more processes from the project page later.'),
        'client_error_messages' => [__('Please enter a process name.')],
        'atom_element' => 'atoms/form_input',
        'atom_data' => [
            'name' => 'process_title',
            'type' => 'text',
            'value' => $project->process_title ?? '',
            'placeholder' => __('e.g., Image Recognition'),
            'required' => true
        ]
    ]) ?>

    <?= $this->element('molecules/form_field', [
        'name' => 'process_description',
        'label' => __('Process Description'),
        'type' => 'textarea',
        'tooltip' => __('Add a brief but meaningful description of what this assessment process is about.'),
        'atom_element' => 'atoms/form_textarea',
        'atom_data' => [
            'name' => 'process_description',
            'value' => $project->process_description ?? '',
            'placeholder' => __('Text')
        ]
    ]) ?>

    <!-- Participants Section -->
    <?= $this->element('organisms/participant_form_rows', ['process' => null, 'project' => $project]) ?>


    <!-- Buttons -->
    <div class="flex gap-4 mt-6">
        <?= $this->element('atoms/button', [
            'label' => __('Cancel'),
            'variant' => 'secondary',
            'type' => 'button',
            'url' => ['controller' => 'Projects', 'action' => 'index']
        ]) ?>

        <?= $this->element('atoms/button', [
            'label' => __('Create Project'),
            'variant' => 'primary',
            'type' => 'submit',
            'options' => ['class' => 'ml-auto', ':disabled' => '!formValid']
        ]) ?>
    </div>

<?= $this->Form->end() ?>

