<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;
use Cake\Http\Client\Response;
use Exception;
use RuntimeException;
use function Cake\Core\env;

/**
 * GitHub Service for Issue Management
 *
 * Provides integration with GitHub API to create issues programmatically.
 * Used for the bug reporting feature to allow users to submit bugs directly
 * from the application.
 *
 * ## Required Environment Variables
 *
 * - `GITHUB_TOKEN` - Personal Access Token with Issues read/write permission
 * - `GITHUB_REPO_OWNER` - Repository owner (defaults to 'Mission-KI')
 * - `GITHUB_REPO_NAME` - Repository name (defaults to 'pruefportal')
 *
 * ## Usage Example
 *
 * ```php
 * $gitHubService = new GitHubService();
 * $result = $gitHubService->createIssue(
 *     title: 'Bug: Button not working',
 *     body: 'Description of the bug...',
 *     labels: ['bug', 'user-reported']
 * );
 * // Returns: ['success' => true, 'issue_url' => 'https://github.com/...', 'issue_number' => 123]
 * ```
 *
 * @see https://docs.github.com/en/rest/issues/issues#create-an-issue
 */
class GitHubService
{
    private const API_BASE_URL = 'https://api.github.com';
    private const API_VERSION = '2022-11-28';

    private Client $httpClient;
    private string $token;
    private string $owner;
    private string $repo;

    /**
     * Initialize the GitHub service
     *
     * Loads configuration from environment variables.
     */
    public function __construct()
    {
        $this->token = (string)env('GITHUB_TOKEN', '');
        $this->owner = (string)env('GITHUB_REPO_OWNER', 'Mission-KI');
        $this->repo = (string)env('GITHUB_REPO_NAME', 'pruefportal');

        $this->httpClient = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => self::API_VERSION,
                'User-Agent' => 'Pruefportal-BugReporter/1.0',
            ],
        ]);
    }

    /**
     * Create a new GitHub issue
     *
     * @param string $title Issue title (required)
     * @param string $body Issue body/description (required)
     * @param array<string> $labels Labels to apply (optional)
     * @return array{success: bool, issue_url?: string, issue_number?: int, error?: string}
     */
    public function createIssue(string $title, string $body, array $labels = []): array
    {
        $this->validateConfiguration();

        $url = sprintf(
            '%s/repos/%s/%s/issues',
            self::API_BASE_URL,
            $this->owner,
            $this->repo,
        );

        $payload = [
            'title' => $title,
            'body' => $body,
        ];

        if (!empty($labels)) {
            $payload['labels'] = $labels;
        }

        try {
            $response = $this->httpClient->post($url, json_encode($payload), [
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to connect to GitHub API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if the service is properly configured
     *
     * @return bool True if all required configuration is present
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->owner) && !empty($this->repo);
    }

    /**
     * Validate that required configuration is present
     *
     * @throws \RuntimeException If configuration is missing
     */
    private function validateConfiguration(): void
    {
        if (empty($this->token)) {
            throw new RuntimeException(
                'GitHub API token not configured. Set GITHUB_TOKEN environment variable.',
            );
        }

        if (empty($this->owner) || empty($this->repo)) {
            throw new RuntimeException(
                'GitHub repository not configured. Set GITHUB_REPO_OWNER and GITHUB_REPO_NAME environment variables.',
            );
        }
    }

    /**
     * Handle the API response
     *
     * @param \Cake\Http\Client\Response $response HTTP response from GitHub API
     * @return array{success: bool, issue_url?: string, issue_number?: int, error?: string}
     */
    private function handleResponse(Response $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getJson();

        if ($statusCode === 201) {
            return [
                'success' => true,
                'issue_url' => $body['html_url'] ?? '',
                'issue_number' => $body['number'] ?? 0,
            ];
        }

        $errorMessage = match ($statusCode) {
            401 => 'Invalid GitHub token. Please check your GITHUB_TOKEN configuration.',
            403 => 'GitHub API rate limit exceeded or insufficient permissions.',
            404 => 'GitHub repository not found. Check GITHUB_REPO_OWNER and GITHUB_REPO_NAME.',
            422 => 'Invalid issue data: ' . ($body['message'] ?? 'Validation failed'),
            default => 'GitHub API error: ' . ($body['message'] ?? "HTTP $statusCode"),
        };

        return [
            'success' => false,
            'error' => $errorMessage,
        ];
    }
}
