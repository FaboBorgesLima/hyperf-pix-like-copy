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
use App\Contract\TokenAttributionInterface;
use App\Service\JwtAttribution;
use Shared\Auth\Contract\TokenVerifierInterface;
use Shared\Auth\Service\JwtVerifier;

/**
 * This file is part of Hyperf.
 *
 * @see     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    // Package-namespace bindings (used by Shared\Auth\Middleware\AuthMiddleware)
    TokenVerifierInterface::class => JwtVerifier::class,
    TokenAttributionInterface::class => JwtAttribution::class,
];
