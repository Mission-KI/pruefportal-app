<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class DropUniqueConstraintOnProjectTitle extends BaseMigration
{
    public function up(): void
    {
        $this->table('projects')
            ->removeIndexByName('projects_title_key')
            ->update();
    }

    public function down(): void
    {
        $this->table('projects')
            ->addIndex(
                $this->index('title')
                    ->setName('projects_title_key')
                    ->setType('unique')
            )
            ->update();
    }
}
