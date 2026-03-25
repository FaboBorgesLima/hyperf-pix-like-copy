<?php

namespace App\Controller;

use App\Model\Account;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller()]
class AccountController
{
    #[GetMapping(path: '/accounts/{id}')]
    public function getAccount(RequestInterface $request, ResponseInterface $response, string $id)
    {
        $account = Account::find($id);

        if (!$account) {
            return $response->json(['message' => 'Account not found'])->withStatus(404);
        }

        return $response->json([
            'id' => $account->id,
            'user_id' => $account->user_id,
            'balance' => $account->balance,
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at,
        ]);
    }
}
