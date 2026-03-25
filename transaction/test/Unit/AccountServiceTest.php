<?php

namespace HyperfTest\Unit;

use HyperfTest\TestCase;
use Swoole\Coroutine;

class AccountServiceTest extends TestCase
{
    protected function getService()
    {
        return $this->container->get(\App\Service\AccountService::class);
    }

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

        $this->expectException(\App\Exception\BusinessException::class);
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
    }

    public function testTransferWithInsufficientBalance(): void
    {
        $service = $this->getService();

        $userId1 = $this->faker()->uuid();
        $account1 = $service->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $service->createAccount($userId2);

        $this->expectException(\App\Exception\BusinessException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $service->transfer($account1, $account2, 2000); // more than available balance
    }

    public function testConcurrentTransfers(): void
    {
        $service = $this->getService();

        $userId1 = $this->faker()->uuid();
        $account1 = $service->createAccount($userId1);

        $userId2 = $this->faker()->uuid();
        $account2 = $service->createAccount($userId2);

        $wg = new \Swoole\Coroutine\WaitGroup();

        $wg->add();
        Coroutine::create(function () use ($service, $account1, $account2, $wg) {
            try {

                Coroutine::sleep($this->faker()->numberBetween(1, 2)); // Simulate random delay before transfer
                $service->transfer($account1, $account2, 500, $this->faker()->numberBetween(1, 2)); // Simulate random delay
            } catch (\Exception $e) {
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
            } catch (\Exception $e) {
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
}
