<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', __('Settings'));
$betaBadge = array (
  'text' => 'BETA',
  'variant' => 'warning',
  'size' => 'md',
  'options' => [
    'class' => 'font-semibold bg-yellow-400 text-gray-900'
  ]
);
?>

<div class="max-w-4xl mx-auto p-6" x-data="accountSettings">
    <!-- Page Heading -->
    <h1 class="display-xs text-brand-deep mb-8"><?= __('Settings') ?></h1>

    <!-- Account Information Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <!-- Card Header -->
        <div class="flex items-center gap-3 mb-6">
            <?= $this->element('atoms/icon', [
                'name' => 'user-edit',
                'size' => 'md',
                'options' => ['class' => 'text-brand-deep']
            ]) ?>
            <h2 class="text-xl text-brand-deep"><?= __('Account') ?></h2>
        </div>

        <?= $this->Form->create($user, ['url' => ['action' => 'updateAccount']]) ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

            <div class="space-y-6">

                <?= $this->FormField->control('salutation', [
                    'type' => 'select',
                    'label' => __('Salutation'),
                    'options' => \App\Model\Enum\Salutation::options(),
                    'default' => $user->salutation,
                    'disabled' => true
                ]) ?>


                <?= $this->FormField->control('email', [ // username
                    'type' => 'email',
                    'label' => __('Email Address'),
                    'icon' => 'mail',
                    'value' => $user->username,
                    'help' => __('Das Ändern der E-Mail Adresse ist derzeit deaktiviert. Wenden Sie sich an den Administrator (pruefportal@acatech.de), wenn Sie Ihre E-Mail Adresse ändern möchten.'),
                    'disabled' => true
                ]) ?>
            </div>


            <div class="space-y-6">

                <?= $this->FormField->control('full_name', [
                    'type' => 'text',
                    'label' => __('Full Name'),
                    'value' => $user->full_name,
                    'disabled' => true
                ]) ?>


                <?= $this->FormField->control('company', [
                    'type' => 'text',
                    'label' => __('Company / Organization'),
                    'value' => $user->company ?? '',
                    'disabled' => true
                ]) ?>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">

            <div x-show="!isEditing">
                <?= $this->element('atoms/button', [
                    'label' => __('Edit'),
                    'variant' => 'primary',
                    'size' => 'SM',
                    'icon' => 'edit',
                    'iconPosition' => 'before',
                    'options' => [
                        'type' => 'button',
                        '@click' => 'startEditing()'
                    ]
                ]) ?>
            </div>

            <div x-show="isEditing" class="flex gap-3">
                <?= $this->element('atoms/button', [
                    'label' => __('Cancel'),
                    'variant' => 'secondary',
                    'size' => 'SM',
                    'options' => [
                        'type' => 'button',
                        '@click' => 'cancelEditing()'
                    ]
                ]) ?>
                <?= $this->element('atoms/button', [
                    'label' => __('Save Changes'),
                    'type' => 'submit',
                    'variant' => 'primary',
                    'size' => 'SM',
                    'icon' => 'save-01',
                    'iconPosition' => 'before'
                ]) ?>
            </div>
        </div>

        <?= $this->Form->end() ?>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6" x-data="{ changingPassword: false }">

        <div class="flex items-center gap-3 mb-6">
            <?= $this->element('atoms/icon', [
                'name' => 'lock-locked',
                'size' => 'md',
                'options' => ['class' => 'text-brand-deep']
            ]) ?>
            <h2 class="text-xl text-brand-deep"><?= __('Password') ?></h2>
        </div>

        <?= $this->Form->create($user, ['url' => ['action' => 'updatePassword']]) ?>

        <div x-show="!changingPassword">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <?= $this->element('atoms/form_input', [
                        'name' => 'password_display',
                        'id' => 'password_display',
                        'type' => 'password',
                        'value' => '••••••••',
                        'disabled' => true,
                        'attributes' => ['class' => 'w-full px-3.5 py-2.5 bg-gray-50 border border-brand-light rounded text-gray-600']
                    ]) ?>
                </div>
                <div class="ml-4">
                    <?= $this->element('atoms/button', [
                        'label' => __('Change'),
                        'variant' => 'primary',
                        'icon' => 'lock-locked',
                        'size' => 'SM',
                        'options' => [
                            'type' => 'button',
                            '@click' => 'changingPassword = true'
                        ]
                    ]) ?>
                </div>
            </div>
        </div>

        <div x-show="changingPassword" x-cloak class="space-y-6" x-data="passwordValidation">
            <?= $this->FormField->control('current_password', [
                'type' => 'password',
                'label' => __('Current Password'),
                'required' => true,
                'help' => __('Enter your current password to verify your identity'),
                '@blur' => 'validateCurrentPassword()'
            ]) ?>

            <div x-show="currentPasswordError" class="text-error-600 text-sm mt-1" x-cloak x-text="currentPasswordError"></div>
            <div x-show="isValidatingCurrentPassword" class="text-gray-500 text-sm mt-1" x-cloak>Verifying password...</div>

            <?= $this->FormField->control('new_password', [
                'type' => 'password',
                'label' => __('New Password'),
                'required' => true,
                'help' => __('min. 8 characters, including at least one number and one special character (?!&$ etc.)'),
                'pattern' => '^(?=.*[0-9])(?=.*[\.,!@#$%^&*?]).{8,}$',
                'minlength' => 8,
                'x-model' => 'newPassword',
                '@input' => 'validateNewPassword()',
                ':disabled' => '!isCurrentPasswordValid',
                'error_messages' => [
                    __('Password must be at least 8 characters and include at least one number and one special character')
                ]
            ]) ?>

            <div x-show="newPasswordError" class="text-error-600 text-sm mt-1" x-cloak x-text="newPasswordError"></div>

            <?= $this->FormField->control('confirm_password', [
                'type' => 'password',
                'label' => __('Confirm New Password'),
                'required' => true,
                'help' => __('Please re-enter your new password'),
                'pattern' => '^(?=.*[0-9])(?=.*[\.,!@#$%^&*?]).{8,}$',
                'minlength' => 8,
                'x-model' => 'confirmPassword',
                '@input' => 'validateConfirmPassword()',
                ':disabled' => '!isCurrentPasswordValid',
                ':class' => "{ 'border-error-600': confirmPasswordError }"
            ]) ?>

            <div x-show="confirmPasswordError" class="text-error-600 text-sm mt-1" x-cloak x-text="confirmPasswordError"></div>

            <div class="flex justify-end gap-3 mt-6">
                <?= $this->element('atoms/button', [
                    'label' => __('Cancel'),
                    'variant' => 'secondary',
                    'size' => 'SM',
                    'options' => [
                        'type' => 'button',
                        '@click' => 'changingPassword = false'
                    ]
                ]) ?>
                <?= $this->element('atoms/button', [
                    'label' => __('Update Password'),
                    'type' => 'submit',
                    'variant' => 'primary',
                    'size' => 'SM',
                    'options' => [
                        ':disabled' => '!isPasswordFormValid'
                    ]
                ]) ?>
            </div>
        </div>

        <?= $this->Form->end() ?>
    </div>


    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">

        <div class="flex items-center gap-3 mb-6">
            <?= $this->element('atoms/icon', [
                'name' => 'bell',
                'size' => 'md',
                'options' => ['class' => 'text-brand-deep']
            ]) ?>
            <h2 class="text-xl text-brand-deep"><?= __('Notification Settings') ?></h2>
        </div>


        <?= $this->Form->create($user, ['url' => ['action' => 'updateAccount']]) ?>
        <div class="space-y-4">
            <div class="flex gap-2 items-start">

            <?= $this->element('atoms/form_checkbox', [
                'name' => 'process_updates',
                'id' => 'process_updates',
                'label' => __('Process Updates via Email'),
                'badge' => $betaBadge,
                'description' => __('Receive important updates about your audit processes.'),
                'checked' => (bool) $user->process_updates
            ]) ?>
            </div>
        <div class="flex gap-2 items-start">


            <?= $this->element('atoms/form_checkbox', [
                'name' => 'comment_notifications',
                'id' => 'comment_notifications',
                'label' => __('Comment Notifications via Email'),
                'badge' => $betaBadge,
                'description' => __('Receive an email when someone comments on your posts.'),
                'checked' => (bool) $user->comment_notifications
            ]) ?>

            </div>
        </div>


        <div class="flex justify-end mt-6">
            <?= $this->element('atoms/button', [
                'label' => __('Save Changes'),
                'type' => 'submit',
                'variant' => 'primary',
                'size' => 'SM',
                'icon' => 'save-01',
                'iconPosition' => 'before'
            ]) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>


    <div class="bg-error-50 border border-error-200 rounded-xl p-6">

        <div class="flex items-center gap-3 mb-4">
            <?= $this->element('atoms/icon', [
                'name' => 'alert-triangle',
                'size' => 'md',
                'options' => ['class' => 'text-error-600']
            ]) ?>
            <h2 class="text-xl text-error-600"><?= __('Delete Account') ?></h2>
        </div>


        <p class="text-error-700 text-base mb-6">
            <strong><?= __('Warning') ?>:</strong> <?= __('After deleting your account, your data cannot be restored. This action is permanent and cannot be undone.') ?>
        </p>


        <?= $this->Form->create(null, ['url' => ['action' => 'deleteAccount']]) ?>
        <div>
            <?= $this->element('atoms/button', [
                'label' => __('Delete Account'),
                'type' => 'submit',
                'variant' => 'error',
                'size' => 'SM',
                'icon' => 'trash-01',
                'iconPosition' => 'before',
                'options' => [
                    'onclick' => 'return confirm("' . __('Are you sure you want to delete your account?') . '")'
                ]
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('accountSettings', () => ({
        isEditing: false,
        originalValues: {},

        /**
         * Enables form editing mode and stores original values for potential cancellation
         */
        startEditing() {
            this.isEditing = true;
            this.originalValues = {
                salutation: document.getElementById('salutation').value,
                //username: document.getElementById('username').value,
                full_name: document.getElementById('full_name').value,
                company: document.getElementById('company').value
            };

            this.updateFieldStates(false);
        },

        /**
         * Cancels form editing and restores original values and field states
         */
        cancelEditing() {
            this.isEditing = false;
            Object.keys(this.originalValues).forEach((key) => {
                const field = document.getElementById(key);
                if (field) {
                    field.value = this.originalValues[key];
                }
            });

            this.updateFieldStates(true);
            this.resetTouchedState();
        },

        /**
         * Updates the disabled state for all form fields
         */
        updateFieldStates(disabled) {
            const fields = ['salutation', 'full_name', 'company']; // , 'username'
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.disabled = disabled;
                }
            });
        },

        /**
         * Resets form fields to pristine state by removing touched classes and dispatching reset events
         */
        resetTouchedState() {
            const fields = ['salutation_id', 'full_name', 'company']; // , 'username'
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.classList.remove('touched');

                    window.dispatchEvent(new CustomEvent('reset-pristine', {
                        detail: { fieldId: fieldId }
                    }));
                }
            });
        },

        init() {
        }
    }));

    Alpine.data('passwordValidation', () => ({
        newPassword: '',
        confirmPassword: '',
        newPasswordError: '',
        confirmPasswordError: '',
        currentPasswordError: '',
        isValidatingCurrentPassword: false,
        isCurrentPasswordValid: false,

        validateNewPassword() {
            if (this.newPassword.length === 0) {
                this.newPasswordError = '';
            } else if (this.newPassword.length < 8) {
                this.newPasswordError = '<?= __('Password must be at least 8 characters long') ?>';
            } else {
                this.newPasswordError = '';
            }
            this.validateConfirmPassword();
        },

        validateConfirmPassword() {
            const confirmField = document.getElementById('confirm_password');
            if (this.confirmPassword.length === 0) {
                this.confirmPasswordError = '';
                confirmField.setCustomValidity('');
            } else if (this.newPassword !== this.confirmPassword) {
                this.confirmPasswordError = '<?= __('Passwords do not match') ?>';
                confirmField.setCustomValidity(this.confirmPasswordError);
            } else {
                this.confirmPasswordError = '';
                confirmField.setCustomValidity('');
            }
        },

        async validateCurrentPassword() {
            const currentPasswordField = document.getElementById('current_password');
            const currentPassword = currentPasswordField.value;

            if (!currentPassword) {
                this.currentPasswordError = '';
                this.isCurrentPasswordValid = false;
                currentPasswordField.setCustomValidity('');
                return;
            }

            this.isValidatingCurrentPassword = true;
            this.currentPasswordError = '';
            this.isCurrentPasswordValid = false;

            try {
                const response = await fetch('/users/ajaxCheckCurrentPassword', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_csrfToken"]')?.value || ''
                    },
                    body: JSON.stringify({ current_password: currentPassword })
                });
                const result = await response.json();
                if (result.valid) {
                    this.currentPasswordError = '';
                    this.isCurrentPasswordValid = true;
                    currentPasswordField.setCustomValidity('');
                } else {
                    this.currentPasswordError = '<?= __('Current password is incorrect') ?>';
                    this.isCurrentPasswordValid = false;
                    currentPasswordField.setCustomValidity(this.currentPasswordError);
                }
            } catch (error) {
                this.currentPasswordError = '<?= __('Unable to verify password') ?>';
                this.isCurrentPasswordValid = false;
                currentPasswordField.setCustomValidity(this.currentPasswordError);
            } finally {
                this.isValidatingCurrentPassword = false;
            }
        },

        get isPasswordFormValid() {
            return this.isCurrentPasswordValid &&
                   this.newPassword.length >= 8 &&
                   this.confirmPassword.length > 0 &&
                   this.newPassword === this.confirmPassword &&
                   !this.newPasswordError &&
                   !this.confirmPasswordError &&
                   !this.currentPasswordError;
        }
    }));
});
</script>
