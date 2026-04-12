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

namespace App\Controller;

use App\Exception\BusinessException;
use App\Model\Account;
use App\Request\TransferRequest;
use App\Service\AccountService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Log\LoggerInterface;

#[Controller]
class AccountController
{
    #[Inject]
    protected LoggerInterface $logger;

    #[Inject]
    private AccountService $accountService;

    #[GetMapping(path: '/accounts/{id}')]
    public function getAccount(RequestInterface $request, ResponseInterface $response, string $id)
    {
        $account = Account::find($id);

        if (! $account || ! $this->accountService->userCanView($request->getHeader('X-User-Id')[0], $account)) {
            throw new BusinessException(404, 'Account not found or access denied');
        }

        return $response->json($this->serilizeAccount($account));
    }

    public function serilizeAccount(Account $account): array
    {
        return [
            'id' => $account->id,
            'user_id' => $account->user_id,
            'balance' => $account->balance,
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at,
        ];
    }

    #[GetMapping(path: '/accounts/users/{user_id}')]
    public function getUserAccount(RequestInterface $request, ResponseInterface $response, string $user_id)
    {
        $account = $this->accountService->getAccountByUserId($user_id);

        if (! $account || ! $this->accountService->userCanView($request->getHeader('X-User-Id')[0], $account)) {
            throw new BusinessException(404, 'Account not found or access denied');
        }

        return $response->json($this->serilizeAccount($account));
    }

    #[PostMapping(path: '/accounts/transfer')]
    public function transfer(TransferRequest $request, ResponseInterface $response)
    {
        $data = $request->validated();

        $fromAccount = $this->accountService->getAccountById($data['from_account_id']);
        $toAccount = $this->accountService->getAccountById($data['to_account_id']);

        if (! $fromAccount || ! $toAccount || ! $this->accountService->userCanTransfer($request->getHeader('X-User-Id')[0], $fromAccount)) {
            throw new BusinessException(404, 'Some account cannot be found or access denied to transfer from this account');
        }

        $this->accountService->transfer($fromAccount, $toAccount, (float) $data['amount']);

        return $response->json(['message' => 'Transfer successful']);
    }
}
