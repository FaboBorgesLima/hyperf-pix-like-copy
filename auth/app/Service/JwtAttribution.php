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

namespace App\Service;

use App\Contract\TokenAttributionInterface;
use Firebase\JWT\JWT;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Shared\Auth\Model\AuthToken;

use function Hyperf\Config\config;

class JwtAttribution implements TokenAttributionInterface
{
    #[Inject]
    protected Redis $redis;

    protected string $algorithm = 'RS256';

    private string $privateKey;

    public function __construct()
    {
        $this->privateKey = config('jwt.private_key');
    }

    public function encode(AuthToken $authToken): string
    {
        $payload = [
            'sub' => $authToken->user_id,
            'exp' => $authToken->expire_at->timestamp,
            'iat' => time(),
            'token' => $authToken->token,
            'user_id' => $authToken->user_id,
        ];

        return JWT::encode($payload, $this->privateKey, $this->algorithm);
    }

    public function blacklist(AuthToken $authToken): void
    {
        $this->redis->setex("blacklist:{$authToken->token}", $authToken->expire_at->diffInSeconds(), '1');
    }
}
