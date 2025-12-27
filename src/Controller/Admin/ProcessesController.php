<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Processes Controller
 *
 * @property \App\Model\Table\ProcessesTable $Processes
 */
class ProcessesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Processes->find()
            ->contain(['Projects', 'Candidates', 'Examiners']);
        $processes = $this->paginate($query);

        $this->set(compact('processes'));
    }

    /**
     * View method
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $process = $this->Processes->get($id, contain: ['Projects', 'Candidates', 'Examiners', 'Criteria', 'Indicators', 'Comments', 'Notifications', 'UsecaseDescriptions', 'Uploads']);
        $this->set(compact('process'));
        $this->set(['qualityDimensionIds' => $this->Processes->Criteria->getQualityDimensionIds()]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $process = $this->Processes->newEmptyEntity();
        if ($this->request->is('post')) {
            $process = $this->Processes->patchEntity($process, $this->request->getData());
            if ($this->Processes->save($process)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $projects = $this->Processes->Projects->find('list', limit: 200)->all();
        $users = $this->Processes->Candidates->find('list', limit: 200)->all();
        $this->set(compact('process', 'projects', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $process = $this->Processes->get($id, contain: ['Projects', 'Candidates', 'Examiners']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $process = $this->Processes->patchEntity($process, $this->request->getData());
            if ($this->Processes->save($process)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $projects = $this->Processes->Projects->find('list', limit: 200)->all();
        $users = $this->Processes->Candidates->find('list', limit: 200)->all();
        $this->set(compact('process', 'projects', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Process id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $process = $this->Processes->get($id);
        if ($this->Processes->delete($process)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
