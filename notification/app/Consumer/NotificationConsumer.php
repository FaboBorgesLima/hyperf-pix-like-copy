<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Log;
use App\Message\NotificationMessage;
use App\Service\NotificationService;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

/**
 * Listens on the "notifications" exchange (direct type) and dispatches
 * each incoming message to the appropriate notification channel via
 * NotificationService.
 *
 * To publish a notification from another service, send a JSON payload to:
 *   Exchange:    notifications
 *   Routing key: notification.send
 *
 * Example payload:
 * {
 *   "type":      "email",
 *   "recipient": "user@example.com",
 *   "subject":   "Your transfer was successful",
 *   "body":      "R$ 100.00 has been credited to your account.",
 *   "metadata":  { "transaction_id": "abc-123" }
 * }
 */
#[Consumer(
    exchange: 'notifications',
    routingKey: 'notification.send',
    queue: 'notification_queue',
    name: 'NotificationConsumer',
    nums: 1,
)]
class NotificationConsumer extends ConsumerMessage
{
    public function __construct(protected NotificationService $notificationService) {}

    public function consumeMessage($data, AMQPMessage $message): Result
    {
        try {
            $notificationMessage = NotificationMessage::fromArray((array) $data);

            Log::info(sprintf(
                '[NotificationConsumer] Received %s notification for recipient: %s',
                $notificationMessage->type,
                $notificationMessage->recipient
            ));

            $this->notificationService->dispatch($notificationMessage);

            return Result::ACK;
        } catch (Throwable $e) {
            Log::error(sprintf(
                '[NotificationConsumer] Failed to process notification: %s',
                $e->getMessage()
            ));

            return Result::NACK;
        }
    }
}
