<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    // Package-namespace bindings (used by Shared\Auth\Middleware\AuthMiddleware)
    Shared\Auth\Contract\TokenVerifierInterface::class => Shared\Auth\Service\JwtVerifier::class,
];
