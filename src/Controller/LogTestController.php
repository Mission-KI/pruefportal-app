<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\Log;
use RuntimeException;

/**
 * Log Test Controller
 * TODO (@sascha): wieder entfernen
 *
 * Test endpoints to generate different log levels for monitoring verification.
 * These endpoints help verify that Loki/Promtail is correctly capturing logs.
 */
class LogTestController extends AppController
{
    /**
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions([
            'info',
            'warning',
            'error',
            'critical',
            'exception',
        ]);
    }

    /**
     * Generate an info log entry (200 OK)
     *
     * @return \Cake\Http\Response
     */
    public function info(): Response
    {
        $message = 'Test INFO log entry at ' . date('Y-m-d H:i:s');
        Log::info($message);

        return $this->jsonResponse('info', $message, 200);
    }

    /**
     * Generate a warning log entry (200 OK)
     *
     * @return \Cake\Http\Response
     */
    public function warning(): Response
    {
        $message = 'Test WARNING log entry at ' . date('Y-m-d H:i:s');
        Log::warning($message);

        return $this->jsonResponse('warning', $message, 200);
    }

    /**
     * Generate an error log entry (500 Error)
     *
     * @return \Cake\Http\Response
     */
    public function error(): Response
    {
        $message = 'Test ERROR log entry at ' . date('Y-m-d H:i:s');
        Log::error($message);

        return $this->jsonResponse('error', $message, 500);
    }

    /**
     * Generate a critical log entry (500 Error)
     *
     * @return \Cake\Http\Response
     */
    public function critical(): Response
    {
        $message = 'Test CRITICAL log entry at ' . date('Y-m-d H:i:s');
        Log::critical($message);

        return $this->jsonResponse('critical', $message, 500);
    }

    /**
     * Throw an actual exception (500 Error with stack trace)
     *
     * @return \Cake\Http\Response
     * @throws \RuntimeException
     */
    public function exception(): Response
    {
        throw new RuntimeException('Test exception for monitoring at ' . date('Y-m-d H:i:s'));
    }

    /**
     * Helper to create JSON response
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param int $status HTTP status code
     * @return \Cake\Http\Response
     */
    private function jsonResponse(string $level, string $message, int $status): Response
    {
        $this->autoRender = false;

        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode([
                'level' => $level,
                'message' => $message,
                'logged_at' => date('c'),
                'log_file' => $level === 'info' ? 'logs/debug.log' : 'logs/error.log',
            ]));
    }
}
