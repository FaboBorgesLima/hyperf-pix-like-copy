<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BusinessException;
use Shared\Auth\Model\AuthToken;
use App\Model\User;

class UserService
{
    public function canView(string $userId, string $targetUserId): bool
    {
        // For simplicity, users can only view their own profile
        return $userId === $targetUserId;
    }
    public function canEdit(string $userId, string $targetUserId): bool
    {
        // For simplicity, users can only edit their own profile
        return $userId === $targetUserId;
    }
    public function canDelete(string $userId, string $targetUserId): bool
    {
        // No user can delete any profile, including their own for history reasons
        return false;
    }

    public function deleteUser(AuthToken $authToken, string $userId): void
    {
        if ($authToken->isExpired() || !$this->canDelete($authToken->user_id, $userId)) {
            throw new BusinessException(403, "Unauthorized");
        }
        User::destroy([$userId]);
    }

    public function getUserProfile(AuthToken $authToken, string $userId): ?User
    {
        if ($authToken->isExpired() || !$this->canView($authToken->user_id, $userId)) {
            throw new BusinessException(403, "Unauthorized");
        }
        return User::find($userId);
    }

    public function updateUserProfile(AuthToken $authToken, string $userId, array $data): ?User
    {
        if ($authToken->isExpired() || !$this->canEdit($authToken->user_id, $userId)) {
            throw new BusinessException(403, "Unauthorized");
        }
        $user = User::find($userId);
        if (!$user) {
            throw new BusinessException(404, "User not found");
        }
        $user->fill($data);
        $user->save();
        return $user;
    }
}
