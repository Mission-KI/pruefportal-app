<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * UsecaseDescriptions Controller
 *
 * @property \App\Model\Table\UsecaseDescriptionsTable $UsecaseDescriptions
 */
class UsecaseDescriptionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->UsecaseDescriptions->find()
            ->contain(['Processes', 'Users']);
        $usecaseDescriptions = $this->paginate($query);

        $this->set(compact('usecaseDescriptions'));
    }

    /**
     * View method
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: ['Processes', 'Users']);
        $this->set(compact('usecaseDescription'));
    }

/**
 * Add method
 *
 * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
 */
//    public function add()
//    {
//        $usecaseDescription = $this->UsecaseDescriptions->newEmptyEntity();
//        if ($this->request->is('post')) {
//            $usecaseDescription = $this->UsecaseDescriptions->patchEntity($usecaseDescription, $this->request->getData());
//            if ($this->UsecaseDescriptions->save($usecaseDescription)) {
//                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));
//
//                return $this->redirect(['action' => 'index']);
//            }
//            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
//        }
//        $processes = $this->UsecaseDescriptions->Processes->find('list', limit: 200)->all();
//        $users = $this->UsecaseDescriptions->Users->find('list', limit: 200)->all();
//        $this->set(compact('usecaseDescription', 'processes', 'users'));
//    }

    /**
     * Edit method
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $usecaseDescription = $this->UsecaseDescriptions->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $usecaseDescription = $this->UsecaseDescriptions->patchEntity($usecaseDescription, $this->request->getData());
            if ($this->UsecaseDescriptions->save($usecaseDescription)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $processes = $this->UsecaseDescriptions->Processes->find('list', limit: 200)->all();
        $users = $this->UsecaseDescriptions->Users->find('list', limit: 200)->all();
        $this->set(compact('usecaseDescription', 'processes', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Usecase Description id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $usecaseDescription = $this->UsecaseDescriptions->get($id);
        if ($this->UsecaseDescriptions->delete($usecaseDescription)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
