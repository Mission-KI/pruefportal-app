<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * Notifications cell
 */
class NotificationsCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string, mixed>
     */
    protected array $_validCellOptions = [];

    /**
     * Display notifications for a user.
     *
     * @param int|null $user_id User ID to filter notifications by.
     * @return void
     */
    public function display(?int $user_id = null): void
    {
        $notifications = $this->fetchTable('Notifications')->find('all')->where(['user_id' => $user_id])->orderByDesc('seen');
        $this->set('notifications', $notifications->toArray());
    }
}
