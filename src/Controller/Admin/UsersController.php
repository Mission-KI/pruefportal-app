<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

/**
 * Admin Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Controller\Component\FilterComponent $Filter
 */
class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Configure the login action to not require authentication, preventing the infinite redirect loop issue
        $this->Authentication->addUnauthenticatedActions(['display', 'login']);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Filter');
    }

    public function changeLanguage()
    {
        $this->request->allowMethod(['post']);
        if ($this->request->is('post') && array_key_exists($this->request->getData()['locale'], $this->languages)) {
            $session = $this->request->getSession();
            $session->write('Config.language', $this->request->getData()['locale']);
        }

        return $this->redirect($this->referer());
    }

    public function display()
    {
        $result = $this->Authentication->getResult();
        if (!$result->isValid()) {
            $this->Flash->adminSuccess(__d('admin', 'Please login'));
            $this->render('login');
        }
        $this->set('user', $this->Authentication->getIdentity());
    }

    /**
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result->isValid()) {
            $redirect = $this->request->getQuery('redirect', ['action' => 'display']);

            return $this->redirect($redirect);
        }
        // display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->adminError(__d('admin', 'Error: Username or password is incorrect'));
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $data = $this->Filter->process($this);
        $filter = $this->Users->newEntity($data['request_data']);
        $query = $this->Users->find(
            'all',
            conditions: $data['paginate_conditions'],
        );
        $users = $this->paginate($query);
        $this->set(compact('users', 'filter'));
    }

    /**
     * View method
     *
     * @param string|null $id Admin/user id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $user = $this->Users->get($id, contain: [
            'Tags', 'Comments', 'Projects', 'Notifications',
        ]);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $tags = $this->Users->Tags->find('list', limit: 200, order: ['Tags.title' => 'ASC'])->all();
        $this->set(compact('user', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Admin/user id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->request->getData('set_new_password') !== '') {
                $user->password = $this->request->getData('set_new_password');
            }
            if ($this->Users->save($user)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $tags = $this->Users->Tags->find('list', limit: 200, order: ['Tags.title' => 'ASC'])->all();
        $this->set(compact('user', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Admin/user id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($id > 1 && $this->Users->delete($user)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
