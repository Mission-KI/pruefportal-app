<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsecaseDescriptionsFixture
 */
class UsecaseDescriptionsFixture extends TestFixture
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
                'step' => 1,
                'version' => 1,
                'description' => 'Use case description step 1.',
                'process_id' => 1,
                'user_id' => 1,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'step' => 2,
                'version' => 1,
                'description' => 'Use case description step 2.',
                'process_id' => 1,
                'user_id' => 1,
                'created' => '2025-01-01 11:00:00',
                'modified' => '2025-01-01 11:00:00',
            ],
        ];
        parent::init();
    }
}
