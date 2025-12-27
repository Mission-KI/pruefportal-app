<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AlterCriterionIdOnIndicators extends BaseMigration
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
        $table->removeColumn('criterion_id');
        $table->removeIndex('criterion_id');
        $table->addColumn('value', 'integer', [
            'default' => null,
            'limit' => 2,
            'null' => false,
        ]);
        $table->addColumn('evidence', 'text', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
