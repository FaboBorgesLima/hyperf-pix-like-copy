<?php

declare(strict_types=1);

namespace App\Contract;

use Shared\Auth\Model\AuthToken;

interface TokenAttributionInterface
{
    public function encode(AuthToken $authToken): string;
    public function blacklist(AuthToken $authToken): void;
}
