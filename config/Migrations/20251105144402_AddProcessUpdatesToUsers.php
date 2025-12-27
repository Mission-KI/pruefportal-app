<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddProcessUpdatesToUsers extends BaseMigration
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
        $table = $this->table('users');
        $table->addColumn('process_updates', 'boolean', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('comment_notifications', 'boolean', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
