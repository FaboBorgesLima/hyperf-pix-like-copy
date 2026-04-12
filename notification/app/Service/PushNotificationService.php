<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\NotificationInterface;
use App\Log;
use App\Message\NotificationMessage;

/**
 * Push notification channel stub.
 *
 * Integrate a real push provider (FCM, APNs, etc.) in send() when needed.
 * The current implementation logs the push payload for development purposes.
 */
class PushNotificationService implements NotificationInterface
{
    public function supports(): string
    {
        return 'push';
    }

    public function send(NotificationMessage $message): bool
    {
        // TODO: integrate a real push provider (e.g. Firebase FCM, Apple APNs).
        Log::info(sprintf(
            '[PushNotificationService][STUB] Token: %s | Subject: %s | Body: %s',
            $message->recipient,
            $message->subject,
            $message->body
        ));

        return true;
    }
}
