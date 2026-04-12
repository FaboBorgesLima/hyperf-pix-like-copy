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

namespace HyperfTest\Feature;

use App\Service\AccountService;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class AccountControllerTest extends HttpTestCase
{
    private AccountService $accountService;

    public function setUp(): void
    {
        parent::setUp();
        $this->accountService = $this->container->get(AccountService::class);
    }

    // ── GET /accounts/{id} ───────────────────────────────────────────────────

    public function testGetAccountSuccess(): void
    {
        $userId = $this->faker()->uuid();
        $account = $this->accountService->createAccount($userId);

        $response = $this->client->get("/accounts/{$account->id}", [], $this->userIdHeader($userId));

        $this->assertEquals($account->id, $response['id']);
        $this->assertEquals($userId, $response['user_id']);
        $this->assertEquals(1000, $response['balance']);
    }

    public function testGetAccountNotFound(): void
    {
        $userId = $this->faker()->uuid();

        $response = $this->client->get('/accounts/' . $this->faker()->uuid(), [], $this->userIdHeader($userId));

        $this->assertNull($response);
    }

    public function testGetAccountDeniedForOtherUser(): void
    {
        $userId = $this->faker()->uuid();
        $account = $this->accountService->createAccount($userId);

        $otherId = $this->faker()->uuid();
        $response = $this->client->get("/accounts/{$account->id}", [], $this->userIdHeader($otherId));

        $this->assertNull($response);
    }

    // ── GET /accounts/users/{user_id} ────────────────────────────────────────

    public function testGetUserAccountSuccess(): void
    {
        $userId = $this->faker()->uuid();
        $account = $this->accountService->createAccount($userId);

        $response = $this->client->get("/accounts/users/{$userId}", [], $this->userIdHeader($userId));

        $this->assertEquals($account->id, $response['id']);
        $this->assertEquals($userId, $response['user_id']);
    }

    public function testGetUserAccountNotFound(): void
    {
        $userId = $this->faker()->uuid();
        $response = $this->client->get("/accounts/users/{$userId}", [], $this->userIdHeader($userId));

        $this->assertNull($response);
    }

    public function testGetUserAccountDeniedForOtherUser(): void
    {
        $userId = $this->faker()->uuid();
        $this->accountService->createAccount($userId);

        $otherId = $this->faker()->uuid();
        $response = $this->client->get("/accounts/users/{$userId}", [], $this->userIdHeader($otherId));

        $this->assertNull($response);
    }

    // ── POST /accounts/transfer ──────────────────────────────────────────────

    public function testTransferSuccess(): void
    {
        $userId1 = $this->faker()->uuid();
        $account1 = $this->accountService->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $this->accountService->createAccount($userId2);

        $response = $this->client->post('/accounts/transfer', [
            'from_account_id' => $account1->id,
            'to_account_id' => $account2->id,
            'amount' => 200,
        ], $this->userIdHeader($userId1));

        $this->assertEquals(800, $this->accountService->getAccountById($account1->id)->balance);
        $this->assertEquals(1200, $this->accountService->getAccountById($account2->id)->balance);
    }

    public function testTransferInsufficientBalance(): void
    {
        $userId1 = $this->faker()->uuid();
        $account1 = $this->accountService->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $this->accountService->createAccount($userId2);

        $response = $this->client->post('/accounts/transfer', [
            'from_account_id' => $account1->id,
            'to_account_id' => $account2->id,
            'amount' => 5000,
        ], $this->userIdHeader($userId1));

        $this->assertNull($response);
    }

    public function testTransferDeniedForNonOwner(): void
    {
        $userId1 = $this->faker()->uuid();
        $account1 = $this->accountService->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $this->accountService->createAccount($userId2);

        // userId2 tries to transfer FROM account1 (not their account)
        $response = $this->client->post('/accounts/transfer', [
            'from_account_id' => $account1->id,
            'to_account_id' => $account2->id,
            'amount' => 100,
        ], $this->userIdHeader($userId2));

        $this->assertNull($response);
    }

    public function testTransferValidationFailsWithMissingFields(): void
    {
        $userId = $this->faker()->uuid();
        $response = $this->client->post('/accounts/transfer', [], $this->userIdHeader($userId));

        $this->assertNull($response);
    }

    public function testTransferValidationFailsWithSameAccount(): void
    {
        $userId = $this->faker()->uuid();
        $account = $this->accountService->createAccount($userId);

        $response = $this->client->post('/accounts/transfer', [
            'from_account_id' => $account->id,
            'to_account_id' => $account->id,
            'amount' => 100,
        ], $this->userIdHeader($userId));

        $this->assertEquals(null, $response);
    }

    private function userIdHeader(string $userId): array
    {
        return ['X-User-Id' => $userId];
    }
}
