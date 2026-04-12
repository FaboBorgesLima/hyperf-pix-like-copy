<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\NotificationInterface;
use App\Log;
use App\Message\NotificationMessage;

/**
 * SMS notification channel stub.
 *
 * Integrate a real SMS provider (Twilio, AWS SNS, etc.) in send() when needed.
 * The current implementation logs the SMS payload for development purposes.
 */
class SmsNotificationService implements NotificationInterface
{
    public function supports(): string
    {
        return 'sms';
    }

    public function send(NotificationMessage $message): bool
    {
        // TODO: integrate a real SMS provider (e.g. Twilio, AWS SNS).
        Log::info(sprintf(
            '[SmsNotificationService][STUB] To: %s | Body: %s',
            $message->recipient,
            $message->body
        ));

        return true;
    }
}
