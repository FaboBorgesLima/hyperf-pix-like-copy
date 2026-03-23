<?php

declare(strict_types=1);

namespace Shared\Auth\Contract;

use Shared\Auth\Model\AuthToken;

interface TokenVerifierInterface
{
    public function decode(string $token): ?AuthToken;
    public function isBlacklisted(string $token): bool;
}
