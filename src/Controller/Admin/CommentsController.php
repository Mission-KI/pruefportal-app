<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Comments Controller
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    protected array $paginate = [
        'limit' => 20,
        'order' => ['process_id' => 'desc', 'Comments.created' => 'asc'],
    ];

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Comments->find()
            ->contain(['Users', 'Processes']);
        $comments = $this->paginate($query);

        $this->set(compact('comments'));
    }

    /**
     * View method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $comment = $this->Comments->get($id, contain: ['Users', 'Processes', 'ChildComments.Users', 'Uploads']);
        $this->set(compact('comment'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $comment = $this->Comments->newEmptyEntity();
        if ($this->request->is('post')) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $users = $this->Comments->Users->find('list', limit: 200)->all();
        $processes = $this->Comments->Processes->find('list', limit: 200)->all();
        $this->set(compact('comment', 'users', 'processes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $comment = $this->Comments->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $users = $this->Comments->Users->find('list', limit: 200)->all();
        $processes = $this->Comments->Processes->find('list', limit: 200)->all();
        $this->set(compact('comment', 'users', 'processes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $comment = $this->Comments->get($id);
        if ($this->Comments->delete($comment)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
