<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AlterTitleOnIndicators extends BaseMigration
{

    /**
     * Revert the changes to the database.
     *
     * This method reverts the changes of the up() method.
     * It adds a unique index on the title column of the indicators table.
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('indicators');
        $table->addIndex([
            'title'
        ], [
            'name' => 'indicators_title_key',
            'unique' => true,
        ]);
        $table->update();
    }

    /**
     * Migrate the database.
     *
     * This method migrates the database by removing the unique index on the title column of the indicators table.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('indicators');
        $table->removeIndex('title');
        $table->update();
    }
}
