<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Data Transfer Object representing a notification message
 * consumed from the RabbitMQ "notifications" exchange.
 *
 * Expected AMQP payload (JSON):
 * {
 *   "type":      "email" | "sms" | "push",
 *   "recipient": "address/phone/device-token",
 *   "subject":   "Optional subject line (used by email channel)",
 *   "body":      "Notification body text",
 *   "metadata":  { ...arbitrary key-value context }
 * }
 */
class NotificationMessage
{
    public function __construct(
        public readonly string $type,
        public readonly string $recipient,
        public readonly string $body,
        public readonly string $subject = '',
        public readonly array  $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'email',
            recipient: $data['recipient'] ?? '',
            body: $data['body'] ?? '',
            subject: $data['subject'] ?? '',
            metadata: $data['metadata'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'type'      => $this->type,
            'recipient' => $this->recipient,
            'body'      => $this->body,
            'subject'   => $this->subject,
            'metadata'  => $this->metadata,
        ];
    }
}
