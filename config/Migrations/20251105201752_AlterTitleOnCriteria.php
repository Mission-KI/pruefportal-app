<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AlterTitleOnCriteria extends BaseMigration
{
    public function down(): void
    {
        $table = $this->table('criteria');
        $table->addIndex([
            'title'
        ], [
            'name' => 'criteria_title_key',
            'unique' => true,
        ]);
        $table->update();
    }

    /**
     * Revert the changes to the database.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('criteria');
        $table->removeIndex('title');
        $table->update();
    }
}
