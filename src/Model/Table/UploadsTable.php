<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Uploads Model
 *
 * Storage operations are handled by UploadService.
 * This table class only manages database records and validation.
 *
 * @property \App\Model\Table\ProcessesTable&\Cake\ORM\Association\BelongsTo $Processes
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\BelongsTo $Comments
 * @property \App\Model\Table\IndicatorsTable&\Cake\ORM\Association\BelongsTo $Indicators
 * @method \App\Model\Entity\Upload newEmptyEntity()
 * @method \App\Model\Entity\Upload newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Upload> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Upload get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Upload findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Upload patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Upload> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Upload|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Upload saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Upload>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Upload>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Upload>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Upload> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Upload>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Upload>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Upload>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Upload> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UploadsTable extends Table
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

        $this->setTable('uploads');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Processes', [
            'foreignKey' => 'process_id',
        ]);
        $this->belongsTo('Comments', [
            'foreignKey' => 'comment_id',
        ]);
        $this->belongsTo('Indicators', [
            'foreignKey' => 'indicator_id',
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
            ->scalar('key')
            ->maxLength('key', 255)
            ->allowEmptyString('key');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('size')
            ->allowEmptyString('size');

        $validator
            ->scalar('location')
            ->maxLength('location', 255)
            ->allowEmptyString('location');

        $validator
            ->scalar('etag')
            ->maxLength('etag', 255)
            ->allowEmptyString('etag');

        $validator
            ->integer('process_id')
            ->allowEmptyString('process_id');

        $validator
            ->integer('comment_id')
            ->allowEmptyString('comment_id');

        $validator
            ->integer('indicator_id')
            ->allowEmptyString('indicator_id');

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
        $rules->add($rules->existsIn(['comment_id'], 'Comments'), ['errorField' => 'comment_id']);
        $rules->add($rules->existsIn(['indicator_id'], 'Indicators'), ['errorField' => 'indicator_id']);

        return $rules;
    }

    /**
     * Find uploads by process ID
     *
     * IMPORTANT: Authorization Check Required
     * ----------------------------------------
     * This finder does NOT perform authorization checks. Callers MUST verify
     * that the current user has permission to access the specified process
     * before using this finder.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to modify.
     * @param int $process The process ID to filter by.
     * @return \Cake\ORM\Query\SelectQuery The modified query.
     */
    public function findByProcess(SelectQuery $query, int $process): SelectQuery
    {
        return $query->where(['process_id' => $process]);
    }
}
