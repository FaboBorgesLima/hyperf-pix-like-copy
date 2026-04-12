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

use App\Model\Account;
use HyperfTest\TestCase;

/**
 * @internal
 * @coversNothing
 */
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
