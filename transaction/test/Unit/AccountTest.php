<?php

namespace HyperfTest\Unit;

use HyperfTest\TestCase;
use App\Model\Account;

class AccountTest extends TestCase
{
    public function testCreateAccount(): void
    {
        $account = Account::create([
            'user_id' => $this->faker()->uuid(),
            'balance' => 1000,
        ]);

        $this->assertNotNull($account);
        $this->assertEquals($account->user_id, $account->user_id);
        $this->assertEquals(1000, $account->balance);
    }

    public function testFindAccount(): void
    {
        $account = Account::create([
            'user_id' => $this->faker()->uuid(),
            'balance' => 1000,
        ]);

        $foundAccount = Account::find($account->id);

        $this->assertNotNull($foundAccount);
        $this->assertEquals($account->id, $foundAccount->id);
        $this->assertEquals($account->user_id, $foundAccount->user_id);
        $this->assertEquals($account->balance, $foundAccount->balance);
    }

    public function testDeleteAccount(): void
    {
        $account = Account::create([
            'user_id' => $this->faker()->uuid(),
            'balance' => 1000,
        ]);

        $account->delete();

        $deletedAccount = Account::find($account->id);
        $this->assertNull($deletedAccount);
    }

    public function testSoftDeleteAccount(): void
    {
        $account = Account::create([
            'user_id' => $this->faker()->uuid(),
            'balance' => 1000,
        ]);

        $account->delete();

        $softDeletedAccount = Account::withTrashed()->find($account->id);
        $this->assertNotNull($softDeletedAccount);
        $this->assertEquals($account->id, $softDeletedAccount->id);
    }
}
