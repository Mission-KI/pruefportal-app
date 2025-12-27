<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Exception\ForbiddenException;

/**
 * Comments Controller
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    use ModelAwareTrait;

    /**
     * Ajax only add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function ajaxAdd($process_id = null)
    {
        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }
        $this->viewBuilder()->setClassName('Ajax');
        $comment = $this->Comments->newEmptyEntity();
        if ($this->request->is('post')) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $data = [
                    'success' => true,
                    'message' => __('The Comment has been saved.'),
                ];

                $this->createNewCommentNotification($comment->process_id, $comment->user_id);
            } else {
                $data = [
                    'success' => false,
                    'errors' => $comment->getErrors(),
                    'message' => __('The Comment could not be saved. Please, try again.'),
                ];
            }
            $this->viewBuilder()->setTemplate('/element/ajax');
            $data = json_encode($data);
            $this->set(compact('data'));
        } else {
            $this->set(compact('comment', 'process_id'));
        }
    }

    /**
     * Ajax only view method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful view, renders view otherwise.
     */
    public function ajaxView($process_id = null, $reference_id = null)
    {
        $this->viewBuilder()->setClassName('Ajax');
        if (!$this->request->is('ajax')) {
            throw new ForbiddenException();
        }
        $query = $this->Comments->find(
            'all',
            contain: ['Users'],
            conditions: ['process_id' => $process_id, 'reference_id' => $reference_id],
            sort: ['Comments.created' => 'DESC'],
        );
        $comments = $query->toArray();
        $first_comment = $query->first();
        $comment_form = $this->Comments->newEmptyEntity();
        $this->set(compact('comments', 'first_comment', 'comment_form', 'reference_id'));
    }

    /**
     * Add a new comment
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($this->request->is('post')) {
            $comment = $this->Comments->newEmptyEntity();
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());

            if ($this->Comments->save($comment)) {
                // Upload attachments
                $files = $this->request->getUploadedFiles();
                $uploadErrors = [];
                if (!empty($files['attachments'])) {
                    $uploadsModel = $this->fetchModel('Uploads');
                    foreach ($files['attachments'] as $i => $file) {
                        $upload = $uploadsModel->newEmptyEntity();
                        $upload = $uploadsModel->patchEntity($upload, [
                            'file_url' => $file,
                            'process_id' => null,
                            'comment_id' => $comment->id,
                        ]);
                        if (!$uploadsModel->save($upload)) {
                            $uploadErrors[] = $file->getClientFilename();
                        }
                    }
                }

                if (!empty($uploadErrors)) {
                    $this->Flash->warning(__(
                        'Comment saved, but some files failed to upload: {0}',
                        implode(', ', $uploadErrors),
                    ));
                } else {
                    $this->Flash->success(__('The Comment has been saved.'));
                }

                $this->createNewCommentNotification($comment->process_id, $comment->user_id);
            } else {
                $this->Flash->error(__('The Comment could not be saved. Please, try again.'));
            }

            return $this->redirect(['controller' => 'Processes', 'action' => 'comments', $comment->process_id]);
        } else {
            throw new ForbiddenException();
        }
    }

    /**
     * Create a notification for a new comment
     *
     * @param int $process_id The process id
     * @param int $user_id The user id
     * @return void
     */
    private function createNewCommentNotification(int $process_id, int $user_id)
    {
        // Create a notification
        $process = $this->Comments->Processes->get(
            $process_id,
            contain: ['Examiners'],
            select: ['id', 'title', 'candidate_user'],
        );

        $notificationModel = $this->fetchModel('Notifications');
        $desc = __('A new comment for the process "{0}" has been added.', $process->title);

        if ($process['candidate_user'] == $user_id) {
            // Comment author is candidate - notify ALL examiners
            if (!empty($process->examiners)) {
                foreach ($process->examiners as $examiner) {
                    $notificationModel->createNotification(
                        __('Notification: Comment added'),
                        $desc,
                        $examiner->id,
                        $process_id,
                    );
                }
            }
        } else {
            // Comment author is examiner - notify candidate only
            $notificationModel->createNotification(
                __('Notification: Comment added'),
                $desc,
                $process['candidate_user'],
                $process_id,
            );
        }
    }
}
