<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndicatorIdToUploads extends BaseMigration
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
        $table = $this->table('uploads');
        $table->addColumn('indicator_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->update();

        $table = $this->table('indicators');
        $table->renameColumn('value', 'quality_dimension_id');
        $table->changeColumn('level_examiner', 'integer', [
            'default' => null,
            'limit' => 10,
            'null' => true,
        ]);
        $table->update();
    }
}
