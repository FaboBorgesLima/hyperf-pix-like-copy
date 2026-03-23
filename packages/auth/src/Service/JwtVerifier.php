<?php

namespace Shared\Auth\Service;

use Carbon\Carbon;
use Shared\Auth\Contract\TokenVerifierInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\Redis\Redis;
use Shared\Auth\Model\AuthToken;

use function Hyperf\Config\config;

class JwtVerifier implements TokenVerifierInterface
{
    public string $publicKey;

    public function __construct(protected Redis $redis)
    {
        $this->publicKey = config('jwt.public_key');
    }
    public function decode(string $token): ?AuthToken
    {
        try {
            $decoded = JWT::decode($token, new Key($this->publicKey, 'RS256'));
            return new AuthToken($decoded->user_id, Carbon::createFromTimestamp($decoded->exp), $decoded->token);
        } catch (\Exception $e) {
            return null;
        }
    }


    public function isBlacklisted(string $token): bool
    {
        $auth = $this->decode($token);
        return $auth ? $this->redis->exists("blacklist:{$auth->token}") > 0 : false;
    }
}
