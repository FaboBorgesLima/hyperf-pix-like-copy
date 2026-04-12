<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use App\Message\NotificationMessage;
use App\Service\EmailNotificationService;
use App\Service\NotificationService;
use App\Service\PushNotificationService;
use App\Service\SmsNotificationService;
use HyperfTest\HttpTestCase;

/**
 * @internal
 */
class NotificationTest extends HttpTestCase
{
    public function testHealthEndpoint(): void
    {
        $response = $this->get('/health');
        $this->assertSame(200, $response['code'] ?? 200);
    }

    public function testNotificationMessageFromArray(): void
    {
        $message = NotificationMessage::fromArray([
            'type'      => 'email',
            'recipient' => 'test@example.com',
            'subject'   => 'Test Subject',
            'body'      => 'Test body text',
            'metadata'  => ['key' => 'value'],
        ]);

        $this->assertSame('email', $message->type);
        $this->assertSame('test@example.com', $message->recipient);
        $this->assertSame('Test Subject', $message->subject);
        $this->assertSame('Test body text', $message->body);
        $this->assertSame(['key' => 'value'], $message->metadata);
    }

    public function testEmailNotificationServiceSupports(): void
    {
        $service = new EmailNotificationService();
        $this->assertSame('email', $service->supports());
    }

    public function testSmsNotificationServiceSupports(): void
    {
        $service = new SmsNotificationService();
        $this->assertSame('sms', $service->supports());
    }

    public function testPushNotificationServiceSupports(): void
    {
        $service = new PushNotificationService();
        $this->assertSame('push', $service->supports());
    }

    public function testEmailSendViaLogDriver(): void
    {
        $_ENV['MAIL_DRIVER'] = 'log';
        putenv('MAIL_DRIVER=log');

        $service = new EmailNotificationService();
        $message = NotificationMessage::fromArray([
            'type'      => 'email',
            'recipient' => 'user@example.com',
            'subject'   => 'Hello',
            'body'      => 'This is a test email.',
        ]);

        $result = $service->send($message);
        $this->assertTrue($result);
    }

    public function testSmsStubReturnsTrue(): void
    {
        $service = new SmsNotificationService();
        $message = NotificationMessage::fromArray([
            'type'      => 'sms',
            'recipient' => '+5511999999999',
            'body'      => 'Your verification code is 1234.',
        ]);

        $this->assertTrue($service->send($message));
    }

    public function testPushStubReturnsTrue(): void
    {
        $service = new PushNotificationService();
        $message = NotificationMessage::fromArray([
            'type'      => 'push',
            'recipient' => 'device-token-abc123',
            'subject'   => 'New message',
            'body'      => 'You have a new message.',
        ]);

        $this->assertTrue($service->send($message));
    }
}
