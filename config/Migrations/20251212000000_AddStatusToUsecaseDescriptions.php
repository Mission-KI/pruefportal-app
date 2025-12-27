<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddStatusToUsecaseDescriptions extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('usecase_descriptions');

        $table->addColumn('status', 'string', [
            'default' => 'draft',
            'limit' => 20,
            'null' => false,
            'after' => 'step'
        ])
        ->addIndex(['status'])
        ->update();

        // Set status for existing records based on process status_id
        $this->execute("
            UPDATE usecase_descriptions ucd
            SET status = CASE
                WHEN EXISTS (
                    SELECT 1 FROM processes p
                    WHERE p.id = ucd.process_id
                    AND p.status_id >= 15
                ) THEN 'submitted'
                ELSE 'draft'
            END
        ");
    }
}
