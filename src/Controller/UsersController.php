<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\ResetPasswordForm;
use App\Model\Enum\Role;
use App\Model\Enum\Salutation;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Mailer\MailerAwareTrait;
use Cake\Utility\Security;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Authentication->allowUnauthenticated(['changeLanguage', 'login', 'resetPassword', 'setNewPassword', 'register', 'activate', 'acceptInvitation']);
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        if ($this->request->getParam('action') === 'register' && isset($this->FormProtection)) {
            $this->FormProtection->setConfig('unlockedFields', ['accept_beta_disclaimer']);
        }
    }

    public function changeLanguage()
    {
        if (array_key_exists($this->request->getQuery('lang'), $this->languages)) {
            $session = $this->request->getSession();
            $session->write('Config.language', $this->request->getQuery('lang'));
        }

        return $this->redirect('/');
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful login, renders view otherwise.
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Flash->success(__('Login successful'));
            $redirect = $this->request->getQuery('redirect', ['controller' => 'Pages', 'action' => 'display', 'home']);

            return $this->redirect($redirect);
        }

        // Display error if user submitted and authentication failed
        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

//    public function afterLogin()
//    {
//        $identity = $this->Authentication->getIdentity();
//        $storageUsed = $identity->storage_used;
//        if ($storageUsed > 5000000) {
//            // Notify users of quota
//            $this->Flash->success(__('You are using {0} storage', Number::toReadableSize($storageUsed)));
//        }
//    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null Redirects to '/' after logout.
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success(__('Logout successful'));
        }

        return $this->redirect('/');
    }

    /**
     * Sets a new password for the user (Link is from e-mail).
     *
     * @param string|null $token The unique token to identify the user.
     * @return \Cake\Http\Response|null Redirects to home page after successful password update.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function setNewPassword(?string $token = null)
    {
        $this->logoutIfLoggedIn();
        $user = $this->getUserByToken($token);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->key = '';
            $user->password = $this->request->getData('set_new_password');
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The new password has been saved'));

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('The new password could not be saved. Please, try again.'));
        }
    }

    /**
     * Resets a user's password
     *
     * @return \Cake\Http\Response|null Redirects to home page after successful reset
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function resetPassword()
    {
        $resetPassword = new ResetPasswordForm();
        if ($this->request->is('post')) {
            $error_msg = __('Your username is incorrect. Please contact the MISSION KI team.');
            $user = $this->Users->findByUsernameAndEnabled($this->request->getData('reset_email'), true)->first();
            $token = substr(Security::hash(Security::randomBytes(25)), 2, 64);

            if (is_object($user) && $user->role->value === 'user') {
                $this->request = $this->request->withData('token', $token);
                $this->request = $this->request->withData('email', $user->username);
                $this->request = $this->request->withData('full_name', $user->full_name);
                $this->request = $this->request->withData('salutation', $user->salutation_name);

                if ($resetPassword->execute($this->request->getData())) {
                    $user->key = $token;
                    $this->Users->save($user);
                    $this->Flash->success(__('An email with your reset password link is on its way to you.'));

                    return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
                } else {
                    $this->Flash->error(__('Error sending password email. Please try again.'));
                }
            } else {
                $this->Flash->error($error_msg);

                return $this->redirect(['action' => 'resetPassword']);
            }
        }
        $this->set(compact('resetPassword'));
    }

    /**
     * Register method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function register()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->enabled = false;
            $user->role = Role::User;
            $user->key = substr(Security::hash(Security::randomBytes(25)), 2, 64);

            if ($this->Users->save($user)) {
                $user->salutation = $user->salutation_name;
                $this->getMailer('User')->send('registerConfirmation', [$user]);
                $this->Flash->success(__('Please open the link in your email to activate your account.'));

                return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
            }
            $this->Flash->error(__('Your Account Data could not be saved. Please, try again.'));
        }
        $salutations = Salutation::options();
        $this->set(compact('user', 'salutations'));
    }

    /**
     * Activate a user by the provided token. (Link is from e-mail).
     *
     * @param string|null $token The unique token to identify the user.
     * @return \Cake\Http\Response|null Redirects to login action after successful activation.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function activate(?string $token = null)
    {
        $this->logoutIfLoggedIn();
        $user = $this->getUserByToken($token, true);
        if (!$user) {
            $this->Flash->error(__('The token is invalid or has expired.'));

            return $this->redirect(['action' => 'login']);
        }
        $user->key = '';
        $user->enabled = true;
        if ($this->Users->save($user)) {
            $this->Flash->success(__('Your Account has been activated'));

            return $this->redirect(['action' => 'login']);
        }
        $this->Flash->error(__('Your Account Data could not be saved. Please, try again.'));

        return $this->redirect('/');
    }

    /**
     * Accept Invitation method. (Link is from e-mail).
     *
     * @param string|null $token User key.
     * @return \Cake\Http\Response|null Redirects on successful activation, renders view otherwise.
     */
    public function acceptInvitation(?string $token = null)
    {
        $this->logoutIfLoggedIn();
        $user = $this->getUserByToken($token);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user->key = '';
            $user->enabled = true;
            $user->password = $this->request->getData('set_new_password');
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The Invitation has been accepted and your account is now activated'));

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Your Account Data could not be saved. Please, try again.'));

            return $this->redirect('/');
        }
    }

    public function view()
    {
        $this->getValidUser();
    }

    /**
     * Update Account method
     *
     * @return \Cake\Http\Response|null Redirects on successful update, renders view otherwise.
     */
    public function updateAccount()
    {
        $user = $this->getValidUser();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your Account Data have been saved'));

                return $this->redirect(['action' => 'view']);
            }
            $this->Flash->error(__('Your Account Data could not be saved. Please, try again.'));
        }
    }

    /**
     * Delete method
     *
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function deleteAccount()
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->getValidUser();
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The User and all related data have been deleted.'));
            $this->Authentication->logout();

            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        } else {
            $this->Flash->error(__('The User could not be deleted. Please, try again.'));

            return $this->redirect(['action' => 'view']);
        }
    }

    /**
     * Validate Current Password method
     *
     * @return \Cake\Http\Response|null Redirects on successful validation, renders view otherwise.
     */
    public function ajaxCheckCurrentPassword()
    {
        if (!$this->request->is('post')) {
            throw new ForbiddenException();
        }
        $isValid = $this->validateCurrentPassword($this->request->getData('current_password'));
        $this->viewBuilder()->setClassName('Ajax');
        $this->viewBuilder()->setTemplate('/element/ajax');
        $data = json_encode([
            'valid' => $isValid,
            'message' => $isValid ? '' : __('The current password is incorrect.'),
        ]);
        $this->set(compact('data'));
    }

    /**
     * Updates the user's password after validating the current password.
     *
     * @return \Cake\Http\Response|null Redirects to the user's profile view.
     */
    public function updatePassword()
    {
        if (!$this->request->is(['patch', 'post', 'put'])) {
            return $this->redirect(['action' => 'view']);
        }

        $currentPassword = $this->request->getData('current_password');
        $newPassword = $this->request->getData('new_password');
        $confirmPassword = $this->request->getData('confirm_password');

        if (!$this->validateCurrentPassword($currentPassword)) {
            $this->Flash->error(__('The current password is incorrect.'));

            return $this->redirect(['action' => 'view']);
        }

        if (!$this->isValidNewPassword($newPassword, $confirmPassword)) {
            $this->Flash->error(__('The new password is invalid or does not match the confirmation.'));

            return $this->redirect(['action' => 'view']);
        }

        $user = $this->Users->patchEntity(
            $this->getValidUser(),
            ['password' => $newPassword],
        );

        if ($this->Users->save($user)) {
            $this->Flash->success(__('Your password has been updated successfully.'));
        } else {
            $this->Flash->error(__('Failed to update your password. Please try again.'));
        }

        return $this->redirect(['action' => 'view']);
    }

    /**
     * Validates if the new password and confirmation match and meet requirements.
     *
     * @param string $newPassword The new password.
     * @param string $confirmPassword The password confirmation.
     * @return bool
     */
    private function isValidNewPassword(string $newPassword, string $confirmPassword): bool
    {
        if (empty($newPassword) || $newPassword !== $confirmPassword) {
            return false;
        }

        // Add any additional password strength validation here if needed
        return strlen($newPassword) >= 8;
    }

    private function validateCurrentPassword($current_pw)
    {
        $user = $this->getValidUser();

        return $current_pw && password_verify($current_pw, $user->password);
    }

    /**
     * Retrieves a valid user entity for the current request.
     *
     * It checks if the user is enabled and has the role 'user'. If the user is not found,
     * it redirects to the home page with an error message.
     *
     * @return \Cake\ORM\Entity|void The user entity or void if redirection occurs.
     */
    private function getValidUser()
    {
        $user = $this->request->getAttribute('identity');
        $conditions = ['id' => $user->id, 'enabled' => true, 'role' => Role::User];
        $user = $this->Users->get($user->id, conditions: $conditions);

        if (!$user) {
            $this->Flash->error(__('The User could not be found. Please, try again.'));

            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'home']);
        }

        $this->set(compact('user'));

        return $user;
    }

    /**
     * Retrieves a user entity by the provided token.
     *
     * @param string $token The unique token to identify the user.
     * @param bool $redirect Whether to redirect if the user is not found.
     * @return \Cake\ORM\Entity The user entity.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    private function getUserByToken(string $token, bool $redirect = false)
    {
        $user = $this->Users->findByKey($token)->first();
        if (!$user) {
            if ($redirect) {
                return false;
            }
            throw new NotFoundException(__('The token is invalid or has expired.'));
        }
        $this->set(compact('user'));

        return $user;
    }

    /**
     * Helper method to logout user if they're currently logged in
     *
     * @return void
     */
    private function logoutIfLoggedIn(): void
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
        }
    }
}
