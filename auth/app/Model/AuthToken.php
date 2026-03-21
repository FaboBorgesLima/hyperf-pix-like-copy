<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;

class AuthToken
{
    public function __construct(
        public string $user_id,
        public Carbon $expire_at,
        public string $token
    ) {
        //
    }

    protected static function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function create(
        User $user,
        Carbon $expireAt
    ): self {
        $authToken = new self($user->id, $expireAt, self::generateToken());
        return $authToken;
    }

    public static function fromArray(array $data): self
    {
        $authToken = new self(
            $data['user_id'],
            Carbon::parse($data['expire_at']),
            $data['token']
        );

        return $authToken;
    }

    public function toArray()
    {
        return [
            'user_id' => $this->user_id,
            'expire_at' => $this->expire_at->toDateTimeString(),
            'token' => $this->token,
        ];
    }

    public function isExpired(): bool
    {
        // Tokens are valid for 7 days
        return $this->expire_at->isPast();
    }

    public function belongsToUser(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
