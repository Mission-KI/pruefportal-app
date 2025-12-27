<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;
use Exception;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/5/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    /**
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Configure the login action to not require authentication, preventing the infinite redirect loop issue
        $this->Authentication->addUnauthenticatedActions(['display', 'health']);
    }

    /**
     * Health check endpoint
     * Verifies application and critical dependencies are operational
     *
     * @return \Cake\Http\Response
     */
    public function health(): Response
    {
        $this->autoRender = false;

        $status = 'ok';
        $checks = [];

        // Check database connectivity
        try {
            $connection = $this->getTableLocator()->get('Users')->getConnection();
            $connection->execute('SELECT 1')->fetchAll();
            $checks['database'] = 'ok';
        } catch (Exception $e) {
            $status = 'error';
            $checks['database'] = 'failed';
            $checks['database_error'] = $e->getMessage();
        }

        // Set appropriate HTTP status code
        $httpStatus = $status === 'ok' ? 200 : 503;

        $response = $this->response
            ->withType('application/json')
            ->withStatus($httpStatus)
            ->withStringBody(json_encode([
                'status' => $status,
                'checks' => $checks,
                'timestamp' => date('c'),
            ]));

        return $response;
    }

    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\View\Exception\MissingTemplateException When the view file could not
     *   be found and in debug mode.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found and not in debug mode.
     * @throws \Cake\View\Exception\MissingTemplateException In debug mode.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }

        // Redirect to login if accessing home page without authentication
        if ($page === 'home' && !$this->Authentication->getIdentity()) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => '/']]);
        }

        // Check if user has processes for empty state logic
        $hasProcesses = false;
        if ($page === 'home' && $this->Authentication->getIdentity()) {
            $userId = $this->Authentication->getIdentity()->get('id');
            $hasProcesses = $this->fetchTable('Processes')
                ->find()
                ->contain(['Projects', 'Examiners'])
                ->leftJoinWith('Examiners', function ($q) use ($userId) {
                    return $q->where(['Examiners.id' => $userId]);
                })
                ->where(['OR' => [
                    ['Projects.user_id' => $userId],
                    ['candidate_user' => $userId],
                    ['Examiners.id IS NOT' => null],
                ]])
                ->distinct(['Processes.id'])
                ->count() > 0;
        }

        $this->set(compact('page', 'subpage', 'hasProcesses'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }
}
