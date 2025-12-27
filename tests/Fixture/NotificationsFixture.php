<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NotificationsFixture
 */
class NotificationsFixture extends TestFixture
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
                'title' => 'Test Notification',
                'description' => 'This is a test notification.',
                'seen' => 0,
                'mailed' => 0,
                'user_id' => 1,
                'process_id' => 1,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Seen Notification',
                'description' => 'This notification has been seen.',
                'seen' => 1,
                'mailed' => 1,
                'user_id' => 1,
                'process_id' => 1,
                'created' => '2025-01-01 11:00:00',
                'modified' => '2025-01-01 11:00:00',
            ],
        ];
        parent::init();
    }
}
