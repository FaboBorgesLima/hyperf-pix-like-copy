<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\NotificationInterface;
use App\Log;
use App\Message\NotificationMessage;
use App\Model\Notification;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Dispatches a NotificationMessage to the correct channel strategy.
 * Strategies are resolved from the DI container by their channel type.
 *
 * To add a new channel:
 *  1. Implement NotificationInterface with the desired `supports()` value.
 *  2. Register it in the $channels map below or bind it via config/autoload/dependencies.php.
 */
class NotificationService
{
    /** @var array<string, class-string<NotificationInterface>> */
    private array $channels = [
        'email' => EmailNotificationService::class,
        'sms'   => SmsNotificationService::class,
        'push'  => PushNotificationService::class,
    ];

    public function __construct(protected ContainerInterface $container) {}

    public function dispatch(NotificationMessage $message): void
    {
        $record = Notification::create([
            'type'      => $message->type,
            'recipient' => $message->recipient,
            'subject'   => $message->subject,
            'body'      => $message->body,
            'metadata'  => $message->metadata,
            'status'    => Notification::STATUS_PENDING,
        ]);

        $strategy = $this->resolveStrategy($message->type);

        if ($strategy === null) {
            Log::warning(sprintf('[NotificationService] No strategy registered for type "%s"', $message->type));
            $record->status = Notification::STATUS_FAILED;
            $record->error_message = sprintf('Unsupported notification type: %s', $message->type);
            $record->save();
            return;
        }

        try {
            $success = $strategy->send($message);

            $record->status = $success ? Notification::STATUS_SENT : Notification::STATUS_FAILED;
            $record->sent_at = $success ? Carbon::now() : null;
            $record->save();
        } catch (Throwable $e) {
            Log::error(sprintf('[NotificationService] Channel "%s" threw: %s', $message->type, $e->getMessage()));
            $record->status = Notification::STATUS_FAILED;
            $record->error_message = $e->getMessage();
            $record->save();
        }
    }

    private function resolveStrategy(string $type): ?NotificationInterface
    {
        if (! isset($this->channels[$type])) {
            return null;
        }

        return $this->container->get($this->channels[$type]);
    }
}
