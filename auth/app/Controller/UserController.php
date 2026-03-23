<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\UpdateUserRequest;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Shared\Auth\Middleware\AuthMiddleware;

#[Middleware(AuthMiddleware::class)]
#[Controller()]
class UserController
{
    #[Inject]
    private UserService $userService;

    #[GetMapping(path: "/users/{id}")]
    public function getUser(RequestInterface $request, ResponseInterface $response, string $id)
    {
        $authToken = $request->getAttribute('auth');
        $user = $this->userService->getUserProfile($authToken, $id);

        if (!$user) {
            return $response->json(['message' => 'User not found'])->withStatus(404);
        }

        return $response->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    // Additional methods for update and delete can be added here
    #[PutMapping(path: "/users/{id}")]
    public function updateUser(UpdateUserRequest $request, ResponseInterface $response, string $id)
    {
        $authToken = $request->getAttribute('auth');
        $data = $request->validated();

        try {
            $user = $this->userService->updateUserProfile($authToken, $id, $data);
            return $response->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            return $response->json(['message' => 'Update failed: ' . $e->getMessage()])->withStatus(400);
        }
    }
}
