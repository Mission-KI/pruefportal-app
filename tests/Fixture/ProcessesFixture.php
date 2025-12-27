<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProcessesFixture
 */
class ProcessesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'title' => 'Test Process',
                'description' => 'A test process for unit testing.',
                'project_id' => 1,
                'status_id' => 1,
                'candidate_user' => 1,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Another Process',
                'description' => 'Another test process.',
                'project_id' => 1,
                'status_id' => 2,
                'candidate_user' => 1,
                'created' => '2025-01-02 10:00:00',
                'modified' => '2025-01-02 10:00:00',
            ],
        ];
        parent::init();
    }
}
