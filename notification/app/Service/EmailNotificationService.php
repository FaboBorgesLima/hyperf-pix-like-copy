<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\NotificationInterface;
use App\Log;
use App\Message\NotificationMessage;

use function Hyperf\Support\env;

/**
 * Email notification channel.
 *
 * MAIL_DRIVER=log  (default, dev) — emails are written to the application log.
 * MAIL_DRIVER=smtp                — emails are sent via PHP's mail() function,
 *                                   which requires a working sendmail / MTA.
 */
class EmailNotificationService implements NotificationInterface
{
    public function supports(): string
    {
        return 'email';
    }

    public function send(NotificationMessage $message): bool
    {
        $driver = env('MAIL_DRIVER', 'log');

        if ($driver === 'log') {
            return $this->sendViaLog($message);
        }

        return $this->sendViaMail($message);
    }

    private function sendViaLog(NotificationMessage $message): bool
    {
        Log::info(sprintf(
            '[EmailNotificationService][LOG] To: %s | Subject: %s | Body: %s',
            $message->recipient,
            $message->subject,
            $message->body
        ));

        return true;
    }

    private function sendViaMail(NotificationMessage $message): bool
    {
        $from     = env('MAIL_FROM', 'noreply@notification.local');
        $fromName = env('MAIL_FROM_NAME', 'Notifications');

        $headers = implode("\r\n", [
            sprintf('From: %s <%s>', $fromName, $from),
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
        ]);

        $sent = mail(
            $message->recipient,
            $message->subject,
            $message->body,
            $headers
        );

        if ($sent) {
            Log::info(sprintf(
                '[EmailNotificationService] Email sent to %s (subject: %s)',
                $message->recipient,
                $message->subject
            ));
        } else {
            Log::error(sprintf(
                '[EmailNotificationService] Failed to send email to %s',
                $message->recipient
            ));
        }

        return $sent;
    }
}
