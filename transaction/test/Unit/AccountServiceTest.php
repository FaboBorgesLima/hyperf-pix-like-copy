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

namespace HyperfTest\Unit;

use App\Exception\BusinessException;
use App\Service\AccountService;
use Exception;
use HyperfTest\TestCase;
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;

/**
 * @internal
 * @coversNothing
 */
class AccountServiceTest extends TestCase
{
    public function testCreateAccount(): void
    {
        $account = $this->getService()->createAccount($this->faker()->uuid());

        $this->assertNotNull($account);
        $this->assertEquals(1000, $account->balance); // default balance
    }

    public function testFindAccountById(): void
    {
        $account = $this->getService()->createAccount($this->faker()->uuid());

        $foundAccount = $this->getService()->getAccountById($account->id);

        $this->assertNotNull($foundAccount);
        $this->assertEquals($account->id, $foundAccount->id);
        $this->assertEquals($account->user_id, $foundAccount->user_id);
        $this->assertEquals($account->balance, $foundAccount->balance);
    }

    public function testUserCannotCreateDuplicateAccount(): void
    {
        $userId = $this->faker()->uuid();
        $this->getService()->createAccount($userId);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Account already exists for this user');

        $this->getService()->createAccount($userId);
    }

    public function testUserCanTransfer(): void
    {
        $service = $this->getService();

        $userId = $this->faker()->uuid();
        $account = $service->createAccount($userId);

        $this->assertTrue($service->userCanTransfer($userId, $account));

        $otherUserId = $this->faker()->uuid();

        $this->assertFalse($service->userCanTransfer($otherUserId, $account));
    }

    public function testUserCanView(): void
    {
        $service = $this->getService();

        $userId = $this->faker()->uuid();
        $account = $service->createAccount($userId);

        $this->assertTrue($service->userCanView($userId, $account));

        $otherUserId = $this->faker()->uuid();

        $this->assertFalse($service->userCanView($otherUserId, $account));
    }

    public function testTransfer(): void
    {
        $service = $this->getService();

        $userId1 = $this->faker()->uuid();
        $account1 = $service->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $service->createAccount($userId2);

        $service->transfer($account1, $account2, 200);

        $updatedAccount1 = $service->getAccountById($account1->id);
        $updatedAccount2 = $service->getAccountById($account2->id);

        $this->assertEquals(800, $updatedAccount1->balance);
        $this->assertEquals(1200, $updatedAccount2->balance);

        $transactionsFrom = $updatedAccount1->fromTransactions()->where('to_account_id', $account2->id)->first();
        $transactionsTo = $updatedAccount2->toTransactions()->where('from_account_id', $account1->id)->first();

        $this->assertNotNull($transactionsFrom);
        $this->assertNotNull($transactionsTo);
        $this->assertEquals(200, $transactionsFrom->amount);
        $this->assertEquals(200, $transactionsTo->amount);
    }

    public function testTransferWithInsufficientBalance(): void
    {
        $service = $this->getService();

        $userId1 = $this->faker()->uuid();
        $account1 = $service->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $service->createAccount($userId2);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $service->transfer($account1, $account2, 2000); // more than available balance
    }

    public function testCannotTransferToSameAccount(): void
    {
        $service = $this->getService();

        $userId = $this->faker()->uuid();
        $account = $service->createAccount($userId);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Cannot transfer to the same account');

        $service->transfer($account, $account, 100); // Attempt to transfer to the same account
    }

    public function testConcurrentTransfers(): void
    {
        $service = $this->getService();

        $userId1 = $this->faker()->uuid();
        $account1 = $service->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $service->createAccount($userId2);

        $wg = new WaitGroup();

        $wg->add();
        Coroutine::create(function () use ($service, $account1, $account2, $wg) {
            try {
                Coroutine::sleep($this->faker()->numberBetween(1, 2)); // Simulate random delay before transfer
                $service->transfer($account1, $account2, 500, $this->faker()->numberBetween(1, 2)); // Simulate random delay
            } catch (Exception $e) {
                $this->log('Transfer 1 failed: ' . $e->getMessage());
            } finally {
                $wg->done();
            }
        });

        $wg->add();
        Coroutine::create(function () use ($service, $account1, $account2, $wg) {
            try {
                Coroutine::sleep($this->faker()->numberBetween(1, 2)); // Simulate random delay before transfer
                $service->transfer($account1, $account2, 700, $this->faker()->numberBetween(1, 2)); // Simulate random delay
            } catch (Exception $e) {
                $this->log('Transfer 2 failed: ' . $e->getMessage());
            } finally {
                $wg->done();
            }
        });
        $wg->wait();

        $updatedAccount1 = $service->getAccountById($account1->id);

        // The total transferred amount cannot exceed the initial balance of 1000, so at least one transfer should fail
        $this->assertTrue($updatedAccount1->balance == 500 || $updatedAccount1->balance == 300); // At least one transfer should succeed
    }

    protected function getService()
    {
        return $this->container->get(AccountService::class);
    }
}
