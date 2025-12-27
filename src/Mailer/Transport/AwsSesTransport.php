<?php
declare(strict_types=1);

namespace App\Mailer\Transport;

use App\Service\EmailService;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;

class AwsSesTransport extends AbstractTransport
{
    protected $emailService;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->emailService = new EmailService();
    }

    public function send(Message $message): array
    {
        $to = $message->getTo();
        $subject = $message->getSubject();
        $html = $message->getBodyHtml() ?: $message->getBodyString();
        $text = $message->getBodyText();

        // Determine the sender type from message headers or use default
        $senderType = $message->getHeaders()['X-Sender-Type'] ?? EmailService::SENDER_DEFAULT;

        $result = $this->emailService->sendEmail(
            recipientEmail: key($to),
            subject: $subject,
            bodyText: $text ?: strip_tags($html),
            bodyHtml: $html,
            senderType: $senderType,
        );

        return ['message' => 'Email sent via AWS SES'];
    }
}
