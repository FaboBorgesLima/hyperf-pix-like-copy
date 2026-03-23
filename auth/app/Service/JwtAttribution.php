<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\TokenAttributionInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Shared\Auth\Model\AuthToken;
use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;

use function Hyperf\Config\config;

class JwtAttribution implements TokenAttributionInterface
{
    #[Inject]
    protected Redis $redis;
    private string $privateKey;
    protected string $algorithm = 'RS256';

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
