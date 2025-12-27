<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'enabled' => 1,
                'username' => 'test@example.com',
                'password' => '$2y$10$abcdefghijklmnopqrstuv',
                'role' => 'user',
                'key' => null,
                'salutation' => 'mr',
                'full_name' => 'Test User',
                'company' => 'Test Company',
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_updates' => 1,
                'comment_notifications' => 1,
            ],
            [
                'enabled' => 1,
                'username' => 'admin@example.com',
                'password' => '$2y$10$abcdefghijklmnopqrstuv',
                'role' => 'admin',
                'key' => null,
                'salutation' => 'ms',
                'full_name' => 'Admin User',
                'company' => 'Admin Company',
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_updates' => 1,
                'comment_notifications' => 1,
            ],
            [
                'enabled' => 0,
                'username' => 'disabled@example.com',
                'password' => '$2y$10$abcdefghijklmnopqrstuv',
                'role' => 'user',
                'key' => 'invitation_token_123',
                'salutation' => null,
                'full_name' => 'Disabled User',
                'company' => 'Disabled Company',
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_updates' => 0,
                'comment_notifications' => 0,
            ],
        ];
        parent::init();
    }
}
