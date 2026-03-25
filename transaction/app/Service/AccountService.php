<?php

namespace App\Service;

use App\Model\Account;
use App\Exception\BusinessException;
use App\Constants\ErrorCode;
use Hyperf\DbConnection\Db;
use Swoole\Coroutine;

class AccountService
{
    public function createAccount(string $userId): ?Account
    {
        if (Account::where('user_id', '=', $userId)->count()) {
            throw new BusinessException(ErrorCode::UNPROCESSABLE_ENTITY, 'Account already exists for this user');
        }

        $account = new Account();
        $account->user_id = $userId;
        $account->balance = 1000.00; // Initial balance
        $account->save();

        return $account;
    }

    public function userCanTransfer(string $userId, Account $from): bool
    {
        return $from->user_id === $userId;
    }

    public function userCanView(string $userId, Account $account): bool
    {
        return $account->user_id === $userId;
    }

    public function getAccountByUserId(string $userId): ?Account
    {
        return Account::where('user_id', '=', $userId)->first();
    }

    public function getAccountById(string $accountId): ?Account
    {
        return Account::where('id', '=', $accountId)->first();
    }

    /**
     * Transfer funds from one account to another with proper transaction handling.
     * @param int $sleep ONLY FOR TESTING PURPOSES: Simulate a delay to test concurrent transfers.
     */
    public function transfer(Account $from, Account $to, float $amount, int $sleep = 0): void
    {
        if ($amount <= 0) {
            throw new BusinessException(ErrorCode::UNPROCESSABLE_ENTITY, 'Transfer amount must be greater than zero');
        }

        Db::beginTransaction();
        try {
            if ($from->balance < $amount) {
                throw new BusinessException(ErrorCode::UNPROCESSABLE_ENTITY, 'Insufficient balance');
            }
            $from->balance -= $amount;
            $to->balance += $amount;

            $count = Db::table('accounts')->where([
                'id' => $from->id,
                'balance' => $from->balance + $amount, // Ensure balance hasn't changed
            ])->update(['balance' => $from->balance]);

            if ($count === 0) {
                throw new BusinessException(ErrorCode::UNPROCESSABLE_ENTITY, 'Failed to update from account balance');
            }

            if ($sleep > 0) {
                Coroutine::sleep($sleep); // Simulate delay for testing concurrency
            }

            $count = Db::table('accounts')->where([
                'id' => $to->id,
                'balance' => $to->balance - $amount, // Ensure balance hasn't changed
            ])->update(['balance' => $to->balance]);

            if ($count === 0) {
                throw new BusinessException(ErrorCode::UNPROCESSABLE_ENTITY, 'Failed to update to account balance');
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw $e;
        }
    }
}
