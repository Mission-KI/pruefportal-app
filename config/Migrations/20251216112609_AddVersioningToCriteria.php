<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddVersioningToCriteria extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('criteria');

        // Add version and phase columns
        $table->addColumn('version', 'integer', [
            'default' => 0,
            'limit' => 2,
            'null' => false
        ])
        ->addColumn('phase', 'string', [
            'default' => 'pna',
            'limit' => 20,
            'null' => false
        ])
        ->update();

        // Drop old UNIQUE constraint on title (too restrictive for versioning)
        $this->execute('ALTER TABLE criteria DROP CONSTRAINT IF EXISTS criteria_title_key');

        // Add composite UNIQUE constraint (allows versioning per process)
        $table->addIndex(['process_id', 'title', 'version'], [
            'unique' => true,
            'name' => 'unique_criterion_version'
        ])
        ->update();

        // Migrate existing data:
        // Criteria in completed PNA (status >= 30) become version=1
        $this->execute("
            UPDATE criteria c
            SET version = 1, phase = 'pna_complete'
            FROM processes p
            WHERE c.process_id = p.id
            AND p.status_id >= 30
        ");

        // Criteria in active PNA (status = 20) stay version=0 (drafts)
        // (Already handled by default value)
    }
}
