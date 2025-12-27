<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Tags seed.
 */
class TagsSeed extends BaseSeed
{
    public function getDependencies(): array
    {
        return [
            'UsersSeed',
        ];
    }

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/migrations/4/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $existingCount = $this->getAdapter()->fetchRow("SELECT COUNT(*) as cnt FROM tags");
        if ($existingCount && $existingCount['cnt'] > 0) {
            return;
        }

        $table = $this->table('tags');
        $data = [
            [
                'title' => 'Test Tag',
            ]
        ];
        $table->insert($data)->save();
    }
}
