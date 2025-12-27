<?php
declare(strict_types=1);

namespace App\Service;

use Aws\Exception\AwsException;
use Cake\Mailer\Mailer;
use function Cake\Core\env;

/**
 * Email Service for AWS SES Transactional Emails
 *
 * Provides a simple interface for sending transactional emails via AWS SES with support
 * for two different sender addresses to handle different email scenarios.
 *
 * ## Sender Types
 *
 * ### 1. Default Sender (SENDER_DEFAULT) - `pruefportal@acatech.de`
 * Use this for transactional emails where user replies are expected and should be received.
 * - Welcome emails
 * - Project invitations
 * - User communications requiring responses
 * - Assessment notifications
 *
 * ### 2. No-Reply Sender (SENDER_NOREPLY) - `no-reply.pruefportal@acatech.de`
 * Use this for automated system notifications where replies are not monitored or needed.
 * - Password reset confirmations
 * - System status updates
 * - Automated reminders
 * - Bulk notifications
 *
 * ## Basic Usage Examples
 *
 * ```php
 * // In a controller
 * $emailService = new EmailService();
 *
 * // Example 1: Send a welcome email (users can reply)
 * $emailService->sendEmail(
 *     recipientEmail: 'user@example.com',
 *     subject: 'Welcome to Prüfportal',
 *     bodyText: 'Hello! Welcome to our platform. Please reply if you need help.',
 *     senderType: EmailService::SENDER_DEFAULT
 * );
 *
 * // Example 2: Send a password reset (no reply needed)
 * $emailService->sendEmail(
 *     recipientEmail: 'user@example.com',
 *     subject: 'Password Reset Confirmation',
 *     bodyText: 'Your password has been reset successfully.',
 *     senderType: EmailService::SENDER_NOREPLY
 * );
 *
 * // Example 3: Send with custom HTML template
 * $htmlBody = $this->viewBuilder()->build([
 *     'template' => 'Email/custom_template'
 * ])->render();
 *
 * $emailService->sendEmail(
 *     recipientEmail: 'user@example.com',
 *     subject: 'Your Assessment Results',
 *     bodyText: 'Plain text version of the message',
 *     bodyHtml: $htmlBody,
 *     senderType: EmailService::SENDER_DEFAULT
 * );
 * ```
 *
 * ## Required Environment Variables
 *
 * AWS Credentials:
 * - `SES_ACCESS_KEY_ID` - AWS IAM access key with SES permissions
 * - `SES_SECRET_ACCESS_KEY` - AWS IAM secret key
 * - `SES_REGION` - AWS region (defaults to 'eu-west-1')
 *
 * Default Sender (pruefportal@acatech.de):
 * - `SES_SENDER_EMAIL` - Default sender email address
 * - `SES_SENDER_NAME` - Default sender display name (defaults to 'Prüfportal')
 *
 * No-Reply Sender (no-reply.pruefportal@acatech.de):
 * - `SES_NOREPLY_EMAIL` - No-reply sender email address
 * - `SES_NOREPLY_NAME` - No-reply sender display name (defaults to 'Prüfportal')
 *
 * @see https://docs.aws.amazon.com/ses/ AWS SES Documentation
 */
class EmailService
{
    /**
     * Default AWS SES region for Europe (Ireland)
     */
    private const DEFAULT_SES_REGION = 'eu-west-1';

    /**
     * Sender type for transactional emails where replies are expected
     * Uses: pruefportal@acatech.de
     */
    public const SENDER_DEFAULT = 'default';

    /**
     * Sender type for automated notifications where replies are not monitored
     * Uses: no-reply.pruefportal@acatech.de
     */
    public const SENDER_NOREPLY = 'noreply';

    /**
     * AWS SES client instance
     */
    private SesClient $sesClient;

    /**
     * Initialize the email service with AWS SES credentials
     *
     * Credentials are loaded from environment variables and validated
     * during the first sendEmail() call.
     */
    public function __construct()
    {
        $this->sesClient = new SesClient([
            'version' => 'latest',
            'region' => env('SES_REGION', self::DEFAULT_SES_REGION),
            'credentials' => [
                'key' => env('SES_ACCESS_KEY_ID', ''),
                'secret' => env('SES_SECRET_ACCESS_KEY', ''),
            ],
        ]);
    }

    /**
     * Send a transactional email via AWS SES
     *
     * Sends an email with both plain text and HTML versions. If no HTML body is provided,
     * one will be automatically generated from the plain text with basic formatting.
     *
     * @param string $recipientEmail The recipient's email address (must be verified if in SES Sandbox)
     * @param string $subject The email subject line
     * @param string $bodyText Plain text version of the email body (always required for accessibility)
     * @param string|null $bodyHtml Optional HTML version of the email body. If null, generated from $bodyText
     * @param string $senderType One of SENDER_DEFAULT or SENDER_NOREPLY (defaults to SENDER_DEFAULT)
     * @return array AWS SES response containing MessageId and other metadata
     * @throws \Exception If AWS credentials are not configured, email addresses are not verified, or sending fails
     * @example Basic usage with default sender
     * ```php
     * $result = $emailService->sendEmail(
     *     recipientEmail: 'user@example.com',
     *     subject: 'Project Invitation',
     *     bodyText: 'You have been invited to join the project.'
     * );
     * ```
     *
     * @example Using no-reply sender for automated notifications
     * ```php
     * $result = $emailService->sendEmail(
     *     recipientEmail: 'user@example.com',
     *     subject: 'Password Reset Complete',
     *     bodyText: 'Your password has been successfully reset.',
     *     senderType: EmailService::SENDER_NOREPLY
     * );
     * ```
     *
     * @example With custom HTML template
     * ```php
     * $htmlBody = '<html><body><h1>Welcome</h1><p>Custom HTML content</p></body></html>';
     * $result = $emailService->sendEmail(
     *     recipientEmail: 'user@example.com',
     *     subject: 'Custom Email',
     *     bodyText: 'Welcome! Custom text content.',
     *     bodyHtml: $htmlBody
     * );
     * ```
     */
    public function sendEmail(
        string $recipientEmail,
        string $subject,
        string $bodyText,
        ?string $bodyHtml = null,
        string $senderType = self::SENDER_DEFAULT,
    ): array {
        $driver = env('EMAIL_DRIVER', 'ses');

        if ($driver === 'smtp') {
            return $this->sendViaSmtp($recipientEmail, $subject, $bodyText, $bodyHtml, $senderType);
        }

        return $this->sendViaSes($recipientEmail, $subject, $bodyText, $bodyHtml, $senderType);
    }

    /**
     * Send email via AWS SES
     */
    private function sendViaSes(
        string $recipientEmail,
        string $subject,
        string $bodyText,
        ?string $bodyHtml,
        string $senderType,
    ): array {
        // Validate configuration before attempting to send
        $this->validateConfiguration();

        // Get the appropriate sender configuration
        $senderConfig = $this->getSenderConfig($senderType);

        // Auto-generate HTML body if not provided
        if ($bodyHtml === null) {
            $bodyHtml = $this->textToHtml($bodyText);
        }

        try {
            $result = $this->sesClient->sendEmail([
                'Source' => sprintf('%s <%s>', $senderConfig['name'], $senderConfig['email']),
                'Destination' => [
                    'ToAddresses' => [$recipientEmail],
                ],
                'Message' => [
                    'Subject' => [
                        'Data' => $subject,
                        'Charset' => 'UTF-8',
                    ],
                    'Body' => [
                        'Text' => [
                            'Data' => $bodyText,
                            'Charset' => 'UTF-8',
                        ],
                        'Html' => [
                            'Data' => $bodyHtml,
                            'Charset' => 'UTF-8',
                        ],
                    ],
                ],
            ]);

            return $result->toArray();
        } catch (AwsException $e) {
            throw new Exception($this->formatAwsError($e));
        }
    }

    /**
     * Send email via SMTP (for self-hosted deployments)
     */
    private function sendViaSmtp(
        string $recipientEmail,
        string $subject,
        string $bodyText,
        ?string $bodyHtml,
        string $senderType,
    ): array {
        $senderConfig = $this->getSenderConfig($senderType);

        $mailer = new Mailer('default');
        $mailer->setTo($recipientEmail)
            ->setSubject($subject)
            ->setFrom($senderConfig['email'], $senderConfig['name'])
            ->setEmailFormat('both');

        if ($bodyHtml !== null) {
            $mailer->viewBuilder()
                ->setVar('content', $bodyHtml)
                ->setTemplate('default');
            $mailer->setViewVars(['textContent' => $bodyText, 'htmlContent' => $bodyHtml]);
        }

        $mailer->deliver($bodyText);

        return ['message' => 'Email sent via SMTP', 'driver' => 'smtp'];
    }

    /**
     * Get sender configuration based on sender type
     *
     * Returns the appropriate email address and display name for the selected sender type.
     * Configuration is loaded from environment variables, with EMAIL_* vars taking precedence
     * over SES_* vars for backward compatibility.
     *
     * @param string $senderType One of SENDER_DEFAULT or SENDER_NOREPLY
     * @return array{email: string, name: string} Array with 'email' and 'name' keys
     */
    private function getSenderConfig(string $senderType): array
    {
        return match ($senderType) {
            self::SENDER_NOREPLY => [
                'email' => env('EMAIL_NOREPLY_EMAIL', env('SES_NOREPLY_EMAIL', '')),
                'name' => env('EMAIL_NOREPLY_NAME', env('SES_NOREPLY_NAME', 'Prüfportal')),
            ],
            default => [
                'email' => env('EMAIL_SENDER_EMAIL', env('SES_SENDER_EMAIL', '')),
                'name' => env('EMAIL_SENDER_NAME', env('SES_SENDER_NAME', 'Prüfportal')),
            ],
        };
    }

    /**
     * Convert plain text to basic HTML
     *
     * Creates a simple HTML email with proper encoding and line break formatting.
     * This is automatically called when no HTML body is provided to sendEmail().
     *
     * @param string $text Plain text content to convert
     * @return string HTML formatted email body
     */
    private function textToHtml(string $text): string
    {
        // Escape special characters to prevent HTML injection
        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Convert line breaks to HTML <br> tags
        $formattedText = nl2br($escapedText);

        // Wrap in a basic HTML template with UTF-8 encoding and readable styling
        return sprintf(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">%s</body></html>',
            $formattedText,
        );
    }

    /**
     * Validate email configuration based on the active driver
     *
     * Ensures all required environment variables are set before attempting to send emails.
     * Called automatically by sendViaSes() before each send operation.
     * SMTP validation is handled by the CakePHP Mailer.
     *
     * @throws \Exception If any required environment variable is missing
     */
    private function validateConfiguration(): void
    {
        $driver = env('EMAIL_DRIVER', 'ses');

        if ($driver === 'ses') {
            if (empty(env('SES_ACCESS_KEY_ID')) || empty(env('SES_SECRET_ACCESS_KEY'))) {
                throw new \Exception('AWS SES credentials not configured. Set SES_ACCESS_KEY_ID and SES_SECRET_ACCESS_KEY environment variables.');
            }
        }

        $senderEmail = env('EMAIL_SENDER_EMAIL', env('SES_SENDER_EMAIL', ''));
        if (empty($senderEmail)) {
            throw new \Exception('Sender email not configured. Set EMAIL_SENDER_EMAIL or SES_SENDER_EMAIL environment variable.');
        }

        $noreplyEmail = env('EMAIL_NOREPLY_EMAIL', env('SES_NOREPLY_EMAIL', ''));
        if (empty($noreplyEmail)) {
            throw new \Exception('No-reply email not configured. Set EMAIL_NOREPLY_EMAIL or SES_NOREPLY_EMAIL environment variable.');
        }
    }

    /**
     * Format AWS error messages with helpful troubleshooting hints
     *
     * Enhances AWS SES error messages with actionable guidance for common issues.
     *
     * @param \Aws\Exception\AwsException $e The AWS exception to format
     * @return string User-friendly error message with troubleshooting guidance
     */
    private function formatAwsError(AwsException $e): string
    {
        $message = $e->getAwsErrorMessage();

        // Common error: Email address not verified in SES
        if (str_contains($message, 'Email address is not verified')) {
            return $message . ' - Please verify the email address in AWS SES Console.';
        }

        // Common error: Recipient not verified in sandbox mode
        if (str_contains($message, 'MessageRejected')) {
            return $message . ' - If you\'re in SES Sandbox mode, verify the recipient email address.';
        }

        return 'AWS SES error: ' . $message;
    }
}
