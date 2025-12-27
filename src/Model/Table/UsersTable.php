<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\Role;
use App\Model\Enum\Salutation;
use Cake\Database\Type\EnumType;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\NotificationsTable&\Cake\ORM\Association\HasMany $Notifications
 * @property \App\Model\Table\ProjectsTable&\Cake\ORM\Association\HasMany $Projects
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsToMany $Tags
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    use MailerAwareTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('role', EnumType::from(Role::class));
        $this->getSchema()->setColumnType('salutation', EnumType::from(Salutation::class));

        $this->addBehavior('Timestamp');

        $this->hasMany('Comments', [
            'foreignKey' => 'user_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->hasMany('Notifications', [
            'foreignKey' => 'user_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->hasMany('Projects', [
            'foreignKey' => 'user_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'users_tags',
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
            ->boolean('enabled')
            ->allowEmptyString('enabled');

        $validator
            ->scalar('username')
            ->email('username')
            ->maxLength('username', 128)
            ->requirePresence('username', 'create')
            ->notEmptyString('username')
            ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table', 'message' => __d('cake', 'This value is already in use')]);

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('role')
            ->allowEmptyString('role');

        $validator
            ->scalar('key')
            ->maxLength('key', 128)
            ->allowEmptyString('key');

        $validator
            ->scalar('salutation')
            ->allowEmptyString('salutation');

        $validator
            ->scalar('full_name')
            ->maxLength('full_name', 128)
            ->requirePresence('full_name', 'create')
            ->notEmptyString('full_name');

        $validator
            ->scalar('company')
            ->maxLength('company', 128)
            ->requirePresence('company', 'create')
            ->notEmptyString('company');

        $validator
            ->boolean('process_updates')
            ->allowEmptyString('process_updates');

        $validator
            ->boolean('comment_notifications')
            ->allowEmptyString('comment_notifications');

        return $validator;
    }

    /**
     * Validation rules for invited users.
     *
     * This validation set is used when creating users via invitation (candidate/examiner).
     * It validates critical fields (username as email, full_name) but does not require
     * company or password since these are auto-generated for invited users.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationInvitedUser(Validator $validator): Validator
    {
        $validator
            ->scalar('username')
            ->email('username', false, 'Please provide a valid email address')
            ->maxLength('username', 128)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('full_name')
            ->maxLength('full_name', 128)
            ->requirePresence('full_name', 'create')
            ->notEmptyString('full_name');

        // Company and password are auto-generated for invited users, so not required
        $validator
            ->scalar('company')
            ->maxLength('company', 128)
            ->allowEmptyString('company');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        return $validator;
    }

    /**
     * Returns a query object that will fetch only enabled users.
     *
     * @param \Cake\ORM\Query $query The query object to be modified.
     * @param array $options The options for the query.
     * @return \Cake\ORM\Query The modified query object.
     */
    public function findActiveUser(Query $query, array $options): Query
    {
        return $query->where(['Users.enabled' => true, 'Users.role =' => Role::User]);
    }

    /**
     * Returns a query object that will fetch only enabled admin users.
     *
     * @param \Cake\ORM\Query $query The query object to be modified.
     * @param array $options The options for the query.
     * @return \Cake\ORM\Query The modified query object.
     */
    public function findActiveAdmin(Query $query, array $options): Query
    {
        return $query->where(['Users.enabled' => true, 'Users.role =' => Role::Admin]);
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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);

        return $rules;
    }

    /**
     * Gets the ID of the user associated with the provided email and name.
     * If the user does not exist, it will be created and an invitation email will be sent.
     *
     * @param string $email The email address of the user.
     * @param string $name The full name of the user.
     * @param string $subject The subject of the invitation email.
     * @return int The ID of the user.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If user creation fails validation.
     */
    public function getCandidateExaminerUserId(string $email, string $name, string $subject): int
    {
        $user = $this->find()
            ->where(['Users.username' => $email])
            ->first();

        if (!$user) {
            $token = substr(Security::hash(Security::randomBytes(25)), 2, 64);
            $user = $this->newEntity([
                'enabled' => false,
                'username' => $email,
                'company' => 'Invited User',
                'salutation' => Salutation::Diverse,
                'password' => $token,
                'key' => $token,
                'full_name' => $name,
            ], ['validate' => 'invitedUser']);
            $user->role = Role::User;

            $user = $this->saveOrFail($user);
            $this->getMailer('User')->send('inviteUser', [$user, $subject]);
        }

        return $user->id;
    }
}
