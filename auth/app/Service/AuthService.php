<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\TokenManagerInterface;
use App\Log;
use App\Model\AuthToken;
use App\Model\User;
use App\Task\HashTask;
use Carbon\Carbon;

class AuthService
{

    public function __construct(
        protected HashTask $hashTask,
        protected TokenManagerInterface $tokenManager
    ) {}

    public function login(
        string $email,
        string $password
    ): ?string {

        $user = User::where('email', $email)->firstOrFail();

        if (!$user || !$this->hashTask->verifyPassword($password, $user->password)) {
            return null;
        }

        $authToken = $this->createTokenForUser($user);


        return $this->tokenManager->encode($authToken);
    }

    public function register(
        string $username,
        string $email,
        string $password
    ): string {
        Log::info("Registering user: {$username} with email: {$email}");

        $passwordHash = $this->hashTask->hashPassword($password);

        $user = User::create([
            'name' => $username,
            'email' => $email,
            'password' => $passwordHash,
        ]);

        return $this->tokenManager->encode($this->createTokenForUser($user));
    }

    public function logout(string $token): void
    {
        $authToken = $this->tokenManager->decode($token);
        if ($authToken) {
            $this->tokenManager->blacklist($authToken);
        }
    }

    public function getUserFromToken(string $token): ?User
    {
        $authToken = $this->validateToken($token);
        if (!$authToken) {
            return null;
        }

        return User::find($authToken->user_id);
    }

    public function validateToken(string $token): ?AuthToken
    {
        if ($this->tokenManager->isBlacklisted($token)) {
            return null;
        }

        $authToken = $this->tokenManager->decode($token);
        if (!$authToken || $authToken->isExpired()) {
            return null;
        }

        return $authToken;
    }


    protected function createTokenForUser(User $user): AuthToken
    {
        $expireAt = Carbon::now()->addDays(7);
        $authToken = AuthToken::create($user, $expireAt);

        return $authToken;
    }
}
