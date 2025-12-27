<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Processes seed.
 */
class ProcessesSeed extends BaseSeed
{
    public function getDependencies(): array
    {
        return [
            'ProjectsSeed',
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
        $existingCount = $this->getAdapter()->fetchRow("SELECT COUNT(*) as cnt FROM processes");
        if ($existingCount && $existingCount['cnt'] > 0) {
            return;
        }

        $table = $this->table('processes');
        $data = [
            [
                'title' => 'Process Test',
                'description' => 'Process description dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'project_id' => 1,
                'status_id' => 0,
                'candidate_user' => 6,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ]
        ];
        $table->insert($data)->save();

        // Add examiners via junction table
        $processesExaminersTable = $this->table('processes_examiners');
        $examinersData = [
            [
                'process_id' => 1,
                'user_id' => 4,
                'created' => date('Y-m-d H:i:s'),
                'created_by' => 1,
            ]
        ];
        $processesExaminersTable->insert($examinersData)->save();
    }
}
