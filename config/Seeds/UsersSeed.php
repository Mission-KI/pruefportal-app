<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Users seed.
 */
class UsersSeed extends BaseSeed
{
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
        $existingCount = $this->getAdapter()->fetchRow("SELECT COUNT(*) as cnt FROM users");
        if ($existingCount && $existingCount['cnt'] > 0) {
            return;
        }

        $data = [
            [
                'enabled' => true,
                'username' => 'admin@example.com',
                'password' => '$2y$12$R4j0.nCumkg0nEHnUkDUueNEuCJdm0NqaaLailjXaigWAFzgr0Y4S',
                'role' => 'admin',
                'key' => '',
                'salutation' => 'mr',
                'full_name' => 'Admin User',
                'company' => 'Example Corp',
                'created' => '2025-07-31 13:04:11.336799',
                'modified' => '2025-07-31 14:56:04.141289',
            ],
            [
                'enabled' => true,
                'username' => 'dev@example.com',
                'password' => '$2y$12$aokoMbHA3.FZ/1VeK0SLAeZSybyVRiVgNeoHB8hZhGZbOKAZm9.r2',
                'role' => 'admin',
                'key' => '',
                'salutation' => 'mr',
                'full_name' => 'Dev Admin',
                'company' => 'Example Corp',
                'created' => '2025-07-31 13:04:11.336799',
                'modified' => '2025-08-13 14:01:14.255468',
            ],
            [
                'enabled' => true,
                'username' => 'user1@example.com',
                'password' => '$2y$12$iX7J2YQdwkaYERwKVr4U6eRfB4zlvVJ79x3OcVKsyNWEfC4s21SSO',
                'role' => 'user',
                'key' => '',
                'salutation' => 'mr',
                'full_name' => 'Test User One',
                'company' => 'Test Company A',
                'created' => '2025-08-04 12:36:47.1146',
                'modified' => '2025-08-05 08:56:31.38189',
            ],
            [
                'enabled' => false,
                'username' => 'disabled@example.com',
                'password' => '$2y$12$k/heEg7kU8HwgXWr4zfYtuYPcAJxOLUxSu8oPGrrrjUorxf57TQVu',
                'role' => 'user',
                'key' => '',
                'salutation' => 'diverse',
                'full_name' => 'Disabled User',
                'company' => 'Test Company B',
                'created' => '2025-08-12 13:50:29.308291',
                'modified' => '2025-08-12 13:50:29.308353',
            ],
            [
                'enabled' => true,
                'username' => 'user2@example.com',
                /** Password: admin123 */
                'password' => '$2y$12$wnocg7KEf2so2DxR2W7TQ.KP/qBYsgHS2p7Uaw2Zh1Vbsnt/C6U6u',
                'role' => 'user',
                'key' => '',
                'salutation' => 'ms',
                'full_name' => 'Test User Two',
                'company' => 'Test Company C',
                'created' => '2025-08-05 11:56:29.66829',
                'modified' => '2025-08-13 13:59:49.910894',
            ],
            [
                'enabled' => true,
                'username' => 'test-user@example.com',
                /** Password: admin123 */
                'password' => '$2y$12$wnocg7KEf2so2DxR2W7TQ.KP/qBYsgHS2p7Uaw2Zh1Vbsnt/C6U6u',
                'role' => 'user',
                'key' => '',
                'salutation' => 'ms',
                'full_name' => 'Test User',
                'company' => 'Test GmbH',
                'created' => '2025-08-05 11:56:29.66829',
                'modified' => '2025-08-13 13:59:49.910894',
            ],
            // FeedbackAI Demo Users
            [
                'enabled' => true,
                'username' => 'l.schmidt@feedbackai.de',
                /** Password: admin123 */
                'password' => '$2y$12$wnocg7KEf2so2DxR2W7TQ.KP/qBYsgHS2p7Uaw2Zh1Vbsnt/C6U6u',
                'role' => 'user',
                'key' => '',
                'salutation' => 'ms',
                'full_name' => 'Lisa Schmidt',
                'company' => 'FeedbackAI UG',
                'created' => '2025-12-18 10:00:00.000000',
                'modified' => '2025-12-18 10:00:00.000000',
            ],
            [
                'enabled' => true,
                'username' => 'm.weber@feedbackai.de',
                /** Password: admin123 */
                'password' => '$2y$12$wnocg7KEf2so2DxR2W7TQ.KP/qBYsgHS2p7Uaw2Zh1Vbsnt/C6U6u',
                'role' => 'user',
                'key' => '',
                'salutation' => 'mr',
                'full_name' => 'Dr. Michael Weber',
                'company' => 'FeedbackAI UG',
                'created' => '2025-12-18 10:00:00.000000',
                'modified' => '2025-12-18 10:00:00.000000',
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
