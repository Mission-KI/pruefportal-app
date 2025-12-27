<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Initial extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('commentaries')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reference_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('title')
                    ->setName('commentaries_title_key')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('reference_id')
                    ->setName('idx_commentaries_reference_id')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_commentaries_user_id')
            )
            ->create();

        $this->table('criteria')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('quality_dimension_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('title')
                    ->setName('criteria_title_key')
                    ->setType('unique')
            )
            ->create();

        $this->table('i18n')
            ->addColumn('locale', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('foreign_key', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('field', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'locale',
                        'model',
                        'foreign_key',
                        'field',
                    ])
                    ->setName('i18n_locale_model_foreign_key_field_key')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index([
                        'locale',
                        'model',
                        'foreign_key',
                        'field',
                    ])
                    ->setName('idx_i18n_lookup')
            )
            ->create();

        $this->table('indicators')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('criterion_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('level_candidate', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('level_examiner', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('title')
                    ->setName('indicators_title_key')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('criterion_id')
                    ->setName('idx_indicators_criterion_id')
            )
            ->create();

        $this->table('notifications')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('seen', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('mailed', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('process_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->create();

        $this->table('processes')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('status_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('candidate_user', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('examiner_user', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('project_id')
                    ->setName('idx_processes_project_id')
            )
            ->create();

        $this->table('projects')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('title')
                    ->setName('projects_title_key')
                    ->setType('unique')
            )
            ->create();

        $this->table('tags')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addIndex(
                $this->index('title')
                    ->setName('tags_title_key')
                    ->setType('unique')
            )
            ->create();

        $this->table('usecase_descriptions')
            ->addColumn('step', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('version', 'integer', [
                'default' => '1',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('process_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('process_id')
                    ->setName('idx_usecase_descriptions_process_id')
            )
            ->create();

        $this->table('users')
            ->addColumn('enabled', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('role', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('key', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('salutation', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('full_name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('company', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addIndex(
                $this->index('username')
                    ->setName('users_username_key')
                    ->setType('unique')
            )
            ->create();

        $this->table('users_tags', ['id' => false, 'primary_key' => ['user_id', 'tag_id']])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_users_tags_user_id')
            )
            ->addIndex(
                $this->index('tag_id')
                    ->setName('idx_users_tags_tag_id')
            )
            ->create();

        $this->table('commentaries')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_user')
            )
            ->update();

        $this->table('indicators')
            ->addForeignKey(
                $this->foreignKey('criterion_id')
                    ->setReferencedTable('criteria')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_criterion')
            )
            ->update();

        $this->table('processes')
            ->addForeignKey(
                $this->foreignKey('project_id')
                    ->setReferencedTable('projects')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_project')
            )
            ->update();

        $this->table('projects')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('NO_ACTION')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_user')
            )
            ->update();

        $this->table('usecase_descriptions')
            ->addForeignKey(
                $this->foreignKey('process_id')
                    ->setReferencedTable('processes')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_process')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_user')
            )
            ->update();

        $this->table('users_tags')
            ->addForeignKey(
                $this->foreignKey('tag_id')
                    ->setReferencedTable('tags')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_tag')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('fk_user')
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
        $this->table('commentaries')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('indicators')
            ->dropForeignKey(
                'criterion_id'
            )->save();

        $this->table('processes')
            ->dropForeignKey(
                'project_id'
            )->save();

        $this->table('projects')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('usecase_descriptions')
            ->dropForeignKey(
                'process_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('users_tags')
            ->dropForeignKey(
                'tag_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('commentaries')->drop()->save();
        $this->table('criteria')->drop()->save();
        $this->table('i18n')->drop()->save();
        $this->table('indicators')->drop()->save();
        $this->table('notifications')->drop()->save();
        $this->table('processes')->drop()->save();
        $this->table('projects')->drop()->save();
        $this->table('tags')->drop()->save();
        $this->table('usecase_descriptions')->drop()->save();
        $this->table('users')->drop()->save();
        $this->table('users_tags')->drop()->save();
    }
}
