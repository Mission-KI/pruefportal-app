<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddVersioningToIndicators extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('indicators');

        // Add version and phase columns
        $table->addColumn('version', 'integer', [
            'default' => 0,
            'limit' => 2,
            'null' => false,
            'after' => 'level_examiner'
        ])
        ->addColumn('phase', 'string', [
            'default' => 'vcio',
            'limit' => 20,
            'null' => false,
            'after' => 'version'
        ])
        ->update();

        // Drop old UNIQUE constraint on title (if exists)
        $this->execute('ALTER TABLE indicators DROP CONSTRAINT IF EXISTS indicators_title_key');

        // Clean up duplicate indicators (keep most recent per process_id, title)
        $this->execute("
            DELETE FROM indicators
            WHERE id NOT IN (
                SELECT MAX(id)
                FROM indicators
                GROUP BY process_id, title
            )
        ");

        // Add composite UNIQUE constraint (allows versioning per process)
        $table->addIndex(['process_id', 'title', 'version'], [
            'unique' => true,
            'name' => 'unique_indicator_version'
        ])
        ->update();

        // Migrate existing data:
        // Indicators in completed VCIO (status >= 35) become version=1
        $this->execute("
            UPDATE indicators i
            SET version = 1, phase = 'vcio_complete'
            FROM processes p
            WHERE i.process_id = p.id
            AND p.status_id >= 35
        ");

        // Indicators in active VCIO (status = 30) stay version=0 (drafts)
        // (Already handled by default value)
    }
}
