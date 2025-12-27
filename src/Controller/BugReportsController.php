<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\GitHubService;
use Authentication\IdentityInterface;
use Cake\Http\Response;
use Exception;

/**
 * BugReports Controller
 *
 * Handles user-submitted bug reports by creating GitHub issues.
 * Authenticated users can report bugs through a simple form,
 * and the reports are automatically converted to GitHub issues
 * with relevant metadata for tracking.
 */
class BugReportsController extends AppController
{
    /**
     * Display the bug report form
     *
     * Renders a form for authenticated users to submit bug reports.
     * The form is pre-populated with system information (app version, current URL)
     * to provide context for developers reviewing the report.
     *
     * @return \Cake\Http\Response|null Redirects unauthenticated users to login
     */
    public function form(): ?Response
    {
        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            $this->Flash->error(__('Please log in to report bugs.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        // Use AJAX layout for modal requests (no outer HTML wrapper)
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
        }

        $appVersion = $this->getAppVersion();

        $this->set('appVersion', $appVersion);
        $this->set('currentUrl', $this->request->referer() ?: '/');

        return null;
    }

    /**
     * Process a new bug report submission
     *
     * Validates the user input, creates a formatted GitHub issue,
     * and returns a JSON response indicating success or failure.
     *
     * @return \Cake\Http\Response|null JSON response with submission result
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setLayout('ajax');

        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            return $this->jsonResponse([
                'success' => false,
                'error' => __('You must be logged in to report bugs.'),
            ], 401);
        }

        $title = trim((string)$this->request->getData('title'));
        $description = trim((string)$this->request->getData('description'));

        $errors = $this->validateInput($title, $description);
        if (!empty($errors)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => implode(' ', $errors),
            ], 422);
        }

        $gitHubService = new GitHubService();
        if (!$gitHubService->isConfigured()) {
            return $this->jsonResponse([
                'success' => false,
                'error' => __('Bug reporting is not configured. Please contact an administrator.'),
            ], 503);
        }

        return $this->createGitHubIssue($gitHubService, $title, $description, $identity);
    }

    /**
     * Read application version from package.json
     *
     * @return string Application version or '0.0.0' if unavailable
     */
    private function getAppVersion(): string
    {
        $packageJsonPath = ROOT . DS . 'package.json';

        if (!file_exists($packageJsonPath)) {
            return '0.0.0';
        }

        $packageJson = json_decode(file_get_contents($packageJsonPath), true);

        return $packageJson['version'] ?? '0.0.0';
    }

    /**
     * Validate bug report input fields
     *
     * @param string $title Bug report title
     * @param string $description Bug report description
     * @return array<string> Array of validation error messages (empty if valid)
     */
    private function validateInput(string $title, string $description): array
    {
        $errors = [];

        if (empty($title)) {
            $errors[] = __('Title is required.');
        } elseif (mb_strlen($title) > 256) {
            $errors[] = __('Title must be 256 characters or less.');
        }

        if (empty($description)) {
            $errors[] = __('Description is required.');
        } elseif (mb_strlen($description) > 65535) {
            $errors[] = __('Description is too long.');
        }

        return $errors;
    }

    /**
     * Create a GitHub issue from the bug report
     *
     * @param \App\Service\GitHubService $gitHubService GitHub service instance
     * @param string $title Bug report title
     * @param string $description Bug report description
     * @param \Authentication\IdentityInterface $identity Current user identity
     * @return \Cake\Http\Response JSON response with creation result
     */
    private function createGitHubIssue(
        GitHubService $gitHubService,
        string $title,
        string $description,
        IdentityInterface $identity,
    ): Response {
        $issueTitle = '[Bug Report] ' . $title;
        $issueBody = $this->formatIssueBody($description, $identity);

        try {
            $result = $gitHubService->createIssue(
                $issueTitle,
                $issueBody,
                ['user-reported'],
            );

            if ($result['success']) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => __('Bug report submitted successfully.'),
                    'issue_url' => $result['issue_url'],
                    'issue_number' => $result['issue_number'],
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'error' => $result['error'] ?? __('Failed to create bug report.'),
            ], 500);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => __('An error occurred while submitting the bug report.'),
            ], 500);
        }
    }

    /**
     * Format the GitHub issue body with user metadata
     *
     * Appends reporter information and timestamp to the user-provided
     * description for tracking and accountability purposes.
     *
     * @param string $description User-provided bug description
     * @param \Authentication\IdentityInterface $identity Current user identity
     * @return string Formatted issue body ready for GitHub
     */
    private function formatIssueBody(string $description, IdentityInterface $identity): string
    {
        $userName = $identity->get('name') ?? 'Unknown';
        $userEmail = $identity->get('email') ?? 'Unknown';

        return $description . "\n\n---\n\n" .
            "**Reported by:** {$userName} ({$userEmail})\n" .
            '**Reported at:** ' . date('Y-m-d H:i:s T');
    }

    /**
     * Build a JSON response with the specified data and HTTP status
     *
     * @param array<string, mixed> $data Response payload
     * @param int $status HTTP status code (default: 200)
     * @return \Cake\Http\Response Configured JSON response
     */
    private function jsonResponse(array $data, int $status = 200): Response
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}
