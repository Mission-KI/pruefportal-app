<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\Role;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Processes Model
 *
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Candidates
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Examiners
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\CriteriaTable&\Cake\ORM\Association\HasMany $Criteria
 * @property \App\Model\Table\IndicatorsTable&\Cake\ORM\Association\HasMany $Indicators
 * @property \App\Model\Table\NotificationsTable&\Cake\ORM\Association\HasMany $Notifications
 * @property \App\Model\Table\UsecaseDescriptionsTable&\Cake\ORM\Association\HasMany $UsecaseDescriptions
 * @method \App\Model\Entity\Process newEmptyEntity()
 * @method \App\Model\Entity\Process newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Process> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Process get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Process findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Process patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Process> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Process|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Process saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Process>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Process>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Process>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Process> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Process>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Process>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Process>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Process> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProcessesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('processes');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Candidates', [
            'className' => 'Users',
            'foreignKey' => 'candidate_user',
            'joinType' => 'LEFT',
            'conditions' => ['Candidates.role' => Role::User->value], // , 'Candidates.enabled' => true
        ]);
        $this->belongsToMany('Examiners', [
            'className' => 'Users',
            'through' => 'ProcessesExaminers',
            'foreignKey' => 'process_id',
            'targetForeignKey' => 'user_id',
            'joinType' => 'LEFT',
            'conditions' => ['Examiners.role' => Role::User->value],
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'process_id',
            'joinType' => 'LEFT',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->hasMany('Criteria', [
            'foreignKey' => 'process_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
            'sort' => ['Criteria.quality_dimension_id' => 'ASC', 'Criteria.question_id' => 'ASC'],
        ]);
        $this->hasMany('Indicators', [
            'foreignKey' => 'process_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->hasMany('Notifications', [
            'foreignKey' => 'process_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
            'sort' => ['Notifications.created' => 'DESC'],
        ]);
        $this->hasMany('Uploads', [
            'foreignKey' => 'process_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->hasMany('UsecaseDescriptions', [
            'foreignKey' => 'process_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
            'sort' => ['UsecaseDescriptions.version' => 'DESC'],
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->integer('project_id')
            ->notEmptyString('project_id');

        $validator
            ->integer('status_id')
            ->requirePresence('status_id', 'create')
            ->notEmptyString('status_id');

        $validator
            ->integer('candidate_user')
            ->allowEmptyString('candidate_user');

        $validator->add('examiners', 'different_from_candidate', [
            'rule' => function ($examiners, $context) {
                if (!isset($context['data']['candidate_user']) || empty($examiners)) {
                    return true;
                }
                foreach ($examiners as $examiner) {
                    $examinerId = is_array($examiner) ? ($examiner['id'] ?? null) : $examiner->id;
                    if ((int)$examinerId === (int)$context['data']['candidate_user']) {
                        return false;
                    }
                }

                return true;
            },
            'message' => 'The candidate and the examiner may not be the same person.',
        ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['project_id'], 'Projects'), ['errorField' => 'project_id']);

        return $rules;
    }

    /**
     * Finds processes associated with a specific candidate user.
     *
     * This method queries the Processes table to retrieve processes
     * where the candidate_user matches the provided user ID in the options.
     * The results are limited to 10 entries.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query object to be modified.
     * @param array $options An array of options, where 'user_id' is required to filter the candidate user.
     * @return \Cake\ORM\Query\SelectQuery The modified query object.
     */
    public function findCandidate(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where(['Processes.candidate_user' => $options['candidate']])->contain(['UsecaseDescriptions' => function (SelectQuery $query) {
            return $query->select(['id', 'version', 'step', 'description', 'process_id'])->orderBy(['UsecaseDescriptions.version' => 'DESC']);
        },
        'Candidates', 'Examiners', 'Projects'])->orderBy([
            'CASE WHEN Processes.status_id > 0 THEN 0 ELSE 1 END' => 'ASC',
            'Processes.modified' => 'DESC',
        ])->limit(10);
    }

    /**
     * Finds processes associated with a specific examiner user.
     *
     * This method queries the Processes table to retrieve processes
     * where the examiner is associated through the ProcessesExaminers junction table.
     * The results are limited to 10 entries.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query object to be modified.
     * @param array $options An array of options, where 'examiner' is required to filter the examiner user.
     * @return \Cake\ORM\Query\SelectQuery The modified query object.
     */
    public function findExaminer(SelectQuery $query, array $options): SelectQuery
    {
        return $query
            ->innerJoinWith('Examiners', function ($q) use ($options) {
                return $q->where(['Examiners.id' => $options['examiner']]);
            })
            ->contain(['UsecaseDescriptions', 'Candidates', 'Projects'])
            ->orderBy([
                'CASE WHEN Processes.status_id > 0 THEN 0 ELSE 1 END' => 'ASC',
                'Processes.modified' => 'DESC',
            ])
            ->limit(10);
    }

    /**
     * Finds processes associated with a specific process ID.
     *
     * This method queries the Processes table to retrieve processes
     * where the process ID matches the provided process ID in the options.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query object to be modified.
     * @param array $options An array of options, where 'process' is required to filter the process ID.
     * @return \Cake\ORM\Query\SelectQuery The modified query object.
     */
    public function findParticipants(SelectQuery $query, array $options): SelectQuery
    {
        return $query->select([
            'Processes.id',
            'Processes.candidate_user',
            'Processes.project_id',
            'Processes.status_id',
            'Projects.title',
            'Candidates.full_name',
        ])->contain([
            'Projects' => ['Users' => ['fields' => ['id', 'full_name']]],
            'Candidates' => ['fields' => ['full_name']],
            'Examiners' => ['fields' => ['full_name']],
        ])->where(['Processes.id' => $options['process']]);
    }
}
