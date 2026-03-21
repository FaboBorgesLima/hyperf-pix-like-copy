<?php

namespace App\Service;

use App\Contract\TokenManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use App\Model\AuthToken;
use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;

use function Hyperf\Config\config;

class JWTManager implements TokenManagerInterface
{
    #[Inject]
    protected Redis $redis;
    private string $privateKey;
    public string $publicKey;

    protected string $algorithm = 'RS256';

    public function __construct()
    {
        $this->privateKey = config('jwt.private_key');
        $this->publicKey = config('jwt.public_key');
    }


    public function encode(AuthToken $authToken): string
    {
        $payload = [
            'sub' => $authToken->user_id,
            'exp' => $authToken->expire_at->timestamp,
            'iat' => time(),
        ];

        return JWT::encode($payload, $this->privateKey, $this->algorithm);
    }

    public function decode(string $token): ?AuthToken
    {
        try {
            $payload = JWT::decode($token, new Key($this->publicKey, $this->algorithm));
            return new AuthToken(
                user_id: $payload->sub,
                expire_at: Carbon::createFromTimestamp($payload->exp),
                token: $token
            );
        } catch (\Exception $e) {
            return null;
        }
    }
    public function blacklist(AuthToken $authToken): void
    {
        $this->redis->setex("blacklist:{$authToken->token}", $authToken->expire_at->diffInSeconds(), '1');
    }
    public function isBlacklisted(string $token): bool
    {
        return $this->redis->exists("blacklist:{$token}") > 0;
    }
}
