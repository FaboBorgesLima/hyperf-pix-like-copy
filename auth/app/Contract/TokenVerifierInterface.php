<?php

namespace App\Contract;

use App\Model\AuthToken;

interface TokenVerifierInterface
{
    public function decode(string $token): ?AuthToken;
    public function isBlacklisted(string $token): bool;
}
