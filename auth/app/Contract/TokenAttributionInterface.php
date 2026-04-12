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

namespace App\Contract;

use Shared\Auth\Model\AuthToken;

interface TokenAttributionInterface
{
    public function encode(AuthToken $authToken): string;

    public function blacklist(AuthToken $authToken): void;
}
