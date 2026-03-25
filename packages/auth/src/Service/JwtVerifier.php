<?php

namespace Shared\Auth\Service;

use Carbon\Carbon;
use Shared\Auth\Contract\TokenVerifierInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\Redis\Redis;
use Shared\Auth\Model\AuthToken;

use function Hyperf\Config\config;

/**
 * for this TokenVerifierInterface implementation, we will use RS256 asymmetric signing with a public/private key pair. The JwtVerifier will use the public key to verify incoming tokens, while the JwtAttribution service (used for token creation) will use the private key to sign tokens. This allows for secure token verification without exposing the private key to the services that only need to verify tokens.
 * 
 * The JwtVerifier will also check if the token is blacklisted in Redis, which allows us to implement token revocation (e.g., on logout) by adding the token to a blacklist with an expiration time matching the token's expiry.
 * 
 * This implementation assumes that the JWT payload includes a 'user_id', 'exp' (expiration time), and 'token' (the original token string) fields. The AuthToken model is a simple data structure to hold these values for use in the application.
 * 
 * You need to have a jwt.php config file with the 'public_key' set to your public key for this to work.
 */
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
