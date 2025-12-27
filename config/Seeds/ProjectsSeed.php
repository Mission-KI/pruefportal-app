<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Projects seed.
 */
class ProjectsSeed extends BaseSeed
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
        $existingCount = $this->getAdapter()->fetchRow("SELECT COUNT(*) as cnt FROM projects");
        if ($existingCount && $existingCount['cnt'] > 0) {
            return;
        }

        $table = $this->table('projects');
        $data = [
            [
                'title' => 'Test Project',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Project Description.',
                'user_id' => 4,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ]
        ];
        $table->insert($data)->save();
    }
}
