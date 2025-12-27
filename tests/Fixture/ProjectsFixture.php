<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProjectsFixture
 */
class ProjectsFixture extends TestFixture
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
                'title' => 'Test Project',
                'description' => 'A test project for unit testing.',
                'user_id' => 1,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Another Project',
                'description' => 'Another test project.',
                'user_id' => 2,
                'created' => '2025-01-02 10:00:00',
                'modified' => '2025-01-02 10:00:00',
            ],
        ];
        parent::init();
    }
}
