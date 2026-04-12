<?php

declare(strict_types=1);

namespace App\Contract;

use App\Message\NotificationMessage;

/**
 * Strategy interface for notification channels.
 * Implement this interface to add support for new notification channels
 * (e.g. email, SMS, push, Slack, webhook, etc.).
 */
interface NotificationInterface
{
    /**
     * Send the notification via this channel.
     *
     * @return bool true on success, false on failure
     */
    public function send(NotificationMessage $message): bool;

    /**
     * Returns the channel type identifier this implementation handles.
     * e.g. "email", "sms", "push"
     */
    public function supports(): string;
}
