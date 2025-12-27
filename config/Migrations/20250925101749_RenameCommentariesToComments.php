<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RenameCommentariesToComments extends BaseMigration
{
    public function up(): void
    {
        $this->table('commentaries')
            ->rename('comments')
            ->update();
    }

    public function down(): void
    {
        $this->table('comments')
            ->rename('commentaries')
            ->update();
    }
}
