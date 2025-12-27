<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Criteria Controller
 *
 * @property \App\Model\Table\CriteriaTable $Criteria
 */
class CriteriaController extends AppController
{
    private array $protectionNeedsAnalysis;
    private array $qualityDimensionIds;

    public function initialize(): void
    {
        parent::initialize();

        $this->protectionNeedsAnalysis = $this->Criteria->getProtectionNeedsAnalysisConfig();
        $this->set(['protectionNeedsAnalysis' => $this->protectionNeedsAnalysis]);
        $this->qualityDimensionIds = $this->Criteria->getQualityDimensionIds($this->protectionNeedsAnalysis);
        $this->set(['qualityDimensionIds' => $this->qualityDimensionIds]);
    }

    /**
     * Calculation method
     *
     * Lists all processes with status_id > 20 and if a process_id is provided in the query string,
     * it will fetch all criteria for this process and set them in the view.
     *
     * @return void
     */
    public function calculation($process_id = null)
    {
        $processes = $this->Criteria->Processes->find('list', limit: 200, where: ['Processes.status_id >' => 20])->orderByDesc('Processes.created')->all();
        if ($process_id || $this->request->getQuery('process_id') > 0) {
            $process_id = $process_id ?? $this->request->getQuery('process_id');
            $process = $this->Criteria->Processes->get($process_id);
            // 30 = VCIO-Einstufung
            if ($process->status_id < 30) {
                $this->Flash->adminError(__('The process is not ready for calculation.'));

                return $this->redirect(['action' => 'calculation']);
            }
            $all_criteria = [];
            foreach ($this->qualityDimensionIds as $quality_dimension_id => $quality_dimension) {
                $all_criteria[$quality_dimension_id] = $this->Criteria->find('all')
                    ->where(['process_id' => $process_id, 'Criteria.quality_dimension_id' => $quality_dimension_id])
                    ->orderByAsc('Criteria.question_id')
                    ->orderByAsc('Criteria.criterion_type_id')
                    ->toArray();
            }
            $relevances = $this->Criteria->calculateRelevances($process_id, $this->criterionTypes);
            $this->set(compact('all_criteria', 'relevances', 'process', 'processes'));
        }
        $this->set(compact('processes'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Criteria->find()
            ->contain(['Processes']);
        $criteria = $this->paginate($query);

        $this->set(compact('criteria'));
    }

    /**
     * View method
     *
     * @param string|null $id Criterion id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $criterion = $this->Criteria->get($id, contain: ['Processes']);
        $this->set(compact('criterion'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $criterion = $this->Criteria->newEmptyEntity();
        if ($this->request->is('post')) {
            $criterion = $this->Criteria->patchEntity($criterion, $this->request->getData());
            if ($this->Criteria->save($criterion)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $processes = $this->Criteria->Processes->find('list', limit: 200)->all();
        $this->set(compact('criterion', 'processes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Criterion id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $criterion = $this->Criteria->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $criterion = $this->Criteria->patchEntity($criterion, $this->request->getData());
            if ($this->Criteria->save($criterion)) {
                $this->Flash->adminSuccess(__d('admin', 'The entry has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->adminError(__d('admin', 'The entry could not be saved. Please, try again.'));
        }
        $processes = $this->Criteria->Processes->find('list', limit: 200)->all();
        $this->set(compact('criterion', 'processes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Criterion id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $criterion = $this->Criteria->get($id);
        if ($this->Criteria->delete($criterion)) {
            $this->Flash->adminSuccess(__d('admin', 'The entry has been deleted.'));
        } else {
            $this->Flash->adminError(__d('admin', 'The entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
