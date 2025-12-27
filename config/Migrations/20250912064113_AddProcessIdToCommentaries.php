<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddProcessIdToCommentaries extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('commentaries');
        $table->addColumn('seen', 'boolean', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('process_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('upload_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->update();
    }
}
