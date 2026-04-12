<?php

declare(strict_types=1);

use App\Contract\NotificationInterface;
use App\Service\EmailNotificationService;

return [
    NotificationInterface::class => EmailNotificationService::class,
];
