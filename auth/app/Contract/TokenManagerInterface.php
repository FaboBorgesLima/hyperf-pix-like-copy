<?php

namespace App\Contract;

use App\Model\AuthToken;

interface TokenManagerInterface
{
    public function encode(AuthToken $authToken): string;
    public function decode(string $token): ?AuthToken;
    public function blacklist(AuthToken $authToken): void;
    public function isBlacklisted(string $token): bool;
}
