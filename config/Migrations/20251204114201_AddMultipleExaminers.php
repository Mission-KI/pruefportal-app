<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMultipleExaminers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('processes_examiners', [
            'id' => false,
            'primary_key' => ['process_id', 'user_id']
        ]);
        $table
            ->addColumn('process_id', 'integer', ['null' => false, 'signed' => true])
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => true])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('created_by', 'integer', ['null' => true, 'signed' => true])
            ->addForeignKey('process_id', 'processes', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_processes_examiners_process'
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_processes_examiners_user'
            ])
            ->addIndex(['process_id'], ['name' => 'idx_processes_examiners_process'])
            ->addIndex(['user_id'], ['name' => 'idx_processes_examiners_user'])
            ->create();

        $this->execute("
            INSERT INTO processes_examiners (process_id, user_id, created, created_by)
            SELECT id, examiner_user, created, 1
            FROM processes
            WHERE examiner_user IS NOT NULL
        ");

        $this->table('processes')->removeColumn('examiner_user')->update();
    }

    public function rollback(): void
    {
        $this->table('processes')
            ->addColumn('examiner_user', 'integer', [
                'null' => true,
                'signed' => true,
                'after' => 'candidate_user'
            ])
            ->update();

        $this->execute("
            UPDATE processes p
            SET examiner_user = (
                SELECT user_id FROM processes_examiners
                WHERE process_id = p.id LIMIT 1
            )
        ");

        $this->table('processes_examiners')->drop()->save();
    }
}
