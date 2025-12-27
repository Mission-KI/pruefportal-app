<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\Role;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UsecaseDescriptions Model
 *
 * @property \App\Model\Table\ProcessesTable&\Cake\ORM\Association\BelongsTo $Processes
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\UsecaseDescription newEmptyEntity()
 * @method \App\Model\Entity\UsecaseDescription newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UsecaseDescription> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UsecaseDescription get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UsecaseDescription findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UsecaseDescription patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UsecaseDescription> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UsecaseDescription|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UsecaseDescription saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UsecaseDescription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UsecaseDescription>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UsecaseDescription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UsecaseDescription> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UsecaseDescription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UsecaseDescription>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UsecaseDescription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UsecaseDescription> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsecaseDescriptionsTable extends Table
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

        $this->setTable('usecase_descriptions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Processes', [
            'foreignKey' => 'process_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'conditions' => [
                'Users.role' => Role::User->value,
                'Users.enabled' => true,
            ],
            'joinType' => 'INNER',
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
            ->integer('step')
            ->requirePresence('step', 'create')
            ->notEmptyString('step');

        $validator
            ->integer('version')
            ->requirePresence('version', 'create')
            ->notEmptyString('version');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->integer('process_id')
            ->notEmptyString('process_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

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
        $rules->add($rules->existsIn(['process_id'], 'Processes'), ['errorField' => 'process_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
