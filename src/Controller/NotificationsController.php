<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;

/**
 * Notifications Controller
 *
 * @property \App\Model\Table\NotificationsTable $Notifications
 */
class NotificationsController extends AppController
{
    public function markAsSeen($id)
    {
        $notification = $this->Notifications->get($id, contain: []);

        // Authorization: user can only mark their own notifications as seen
        $userId = $this->request->getAttribute('identity')->id;
        if ($notification->user_id !== $userId) {
            throw new ForbiddenException(__('You are not authorized to modify this notification.'));
        }

        if ($notification && $this->request->is('ajax')) {
            $notification->seen = true;
            $this->Notifications->save($notification);
            $this->viewBuilder()->setClassName('Ajax');
            $this->viewBuilder()->setTemplate('/element/ajax');
            $data = json_encode(['success' => true, 'seen' => true]);
            $this->set(compact('data'));
        } else {
            throw new ForbiddenException();
        }
    }
}
