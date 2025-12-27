<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Indicators Controller
 *
 * @property \App\Model\Table\IndicatorsTable $Indicators
 */
class IndicatorsController extends AppController
{
    private array $qualityDimensionIds;

    public function initialize(): void
    {
        parent::initialize();

        $vcioConfig = $this->Indicators->getVcioConfig();
        $this->qualityDimensionIds = $this->Indicators->getQualityDimensionIds($vcioConfig);
        $this->set(['qualityDimensionIds' => $this->qualityDimensionIds]);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Indicators->find()
            ->contain(['Processes']);
        $indicators = $this->paginate($query);

        $this->set(compact('indicators'));
    }

    /**
     * View method
     *
     * @param string|null $id Indicator id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $indicator = $this->Indicators->get($id, contain: ['Processes', 'Uploads']);
        $this->set(compact('indicator'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $indicator = $this->Indicators->newEmptyEntity();
        if ($this->request->is('post')) {
            $indicator = $this->Indicators->patchEntity($indicator, $this->request->getData());
            if ($this->Indicators->save($indicator)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $processes = $this->Indicators->Processes->find('list', limit: 200)->all();
        $this->set(compact('indicator', 'processes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Indicator id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $indicator = $this->Indicators->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $indicator = $this->Indicators->patchEntity($indicator, $this->request->getData());
            if ($this->Indicators->save($indicator)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $processes = $this->Indicators->Processes->find('list', limit: 200)->all();
        $this->set(compact('indicator', 'processes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Indicator id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $indicator = $this->Indicators->get($id);
        if ($this->Indicators->delete($indicator)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
