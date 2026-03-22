<?php

namespace Tests\Unit;

use App\Exception\BusinessException;
use App\Model\AuthToken;
use App\Service\UserService;
use Carbon\Carbon;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private function service(): UserService
    {
        return $this->container->get(UserService::class);
    }

    // --- canView ---

    public function testCanViewOwnProfile(): void
    {
        $user = $this->createUser();
        $this->assertTrue($this->service()->canView($user->id, $user->id));
    }

    public function testCanViewOtherProfile(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $this->assertFalse($this->service()->canView($user1->id, $user2->id));
    }

    // --- canEdit ---

    public function testCanEditOwnProfile(): void
    {
        $user = $this->createUser();
        $this->assertTrue($this->service()->canEdit($user->id, $user->id));
    }

    public function testCanEditOtherProfile(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $this->assertFalse($this->service()->canEdit($user1->id, $user2->id));
    }

    // --- canDelete ---

    public function testCanDeleteAlwaysReturnsFalse(): void
    {
        $user = $this->createUser();
        $this->assertFalse($this->service()->canDelete($user->id, $user->id));
    }

    // --- getUserProfile ---

    public function testGetUserProfile(): void
    {
        $user = $this->createUser();
        $authToken = AuthToken::create($user, Carbon::now()->addHour());

        $profile = $this->service()->getUserProfile($authToken, $user->id);

        $this->assertNotNull($profile);
        $this->assertEquals($user->id, $profile->id);
    }

    public function testGetUserProfileWithExpiredToken(): void
    {
        $user = $this->createUser();
        $authToken = AuthToken::create($user, Carbon::now()->subSecond());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $this->service()->getUserProfile($authToken, $user->id);
    }

    public function testGetUserProfileUnauthorized(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $authToken = AuthToken::create($user1, Carbon::now()->addHour());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $this->service()->getUserProfile($authToken, $user2->id);
    }

    // --- updateUserProfile ---

    public function testUpdateUserProfile(): void
    {
        $user = $this->createUser();
        $authToken = AuthToken::create($user, Carbon::now()->addHour());

        $newName = $this->faker()->name();
        $updatedUser = $this->service()->updateUserProfile($authToken, $user->id, ['name' => $newName]);

        $this->assertEquals($newName, $updatedUser->name);
    }

    public function testUpdateUserProfileWithExpiredToken(): void
    {
        $user = $this->createUser();
        $authToken = AuthToken::create($user, Carbon::now()->subSecond());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $this->service()->updateUserProfile($authToken, $user->id, ['name' => 'new name']);
    }

    public function testUpdateUserProfileUnauthorized(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $authToken = AuthToken::create($user1, Carbon::now()->addHour());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $this->service()->updateUserProfile($authToken, $user2->id, ['name' => 'new name']);
    }

    public function testUpdateUserProfileNonExistentUser(): void
    {
        $user = $this->createUser();
        // Craft a token pointing to a non-existent user so canEdit passes (same id) but find returns null
        $fakeId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $ghostToken = new AuthToken($fakeId, Carbon::now()->addHour(), 'fake-token');

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(404);

        $this->service()->updateUserProfile($ghostToken, $fakeId, ['name' => 'ghost']);
    }

    // --- deleteUser ---

    public function testDeleteUserAlwaysUnauthorized(): void
    {
        $user = $this->createUser();
        $authToken = AuthToken::create($user, Carbon::now()->addHour());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $this->service()->deleteUser($authToken, $user->id);
    }

    public function testDeleteUserWithExpiredToken(): void
    {
        $user = $this->createUser();
        $authToken = AuthToken::create($user, Carbon::now()->subSecond());

        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(403);

        $this->service()->deleteUser($authToken, $user->id);
    }
}
