<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddQualificationConfirmedToProcessesExaminers extends AbstractMigration
{
    /**
     * Add qualification_confirmed column to processes_examiners table.
     * This tracks whether the candidate has confirmed the examiner's qualifications
     * for high-risk processes requiring validation.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('processes_examiners');
        $table
            ->addColumn('qualification_confirmed', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Indicates if candidate confirmed examiner qualifications for high-risk validation'
            ])
            ->update();
    }

    /**
     * Rollback method to remove the qualification_confirmed column.
     *
     * @return void
     */
    public function rollback(): void
    {
        $table = $this->table('processes_examiners');
        $table->removeColumn('qualification_confirmed')->update();
    }
}
