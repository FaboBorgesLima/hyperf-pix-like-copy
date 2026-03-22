<?php

declare(strict_types=1);

namespace App\Controller;

use App\Log;
use App\Middleware\AuthMiddleware;
use App\Model\AuthToken;
use App\Model\User;
use App\Request\LoginRequest;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use App\Service\AuthService;
use Hyperf\Di\Annotation\Inject;
use App\Request\RegisterRequest;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

#[AutoController(prefix: "/auth")]
class AuthController
{
    #[Inject]
    protected AuthService $authService;

    #[PostMapping(path: "/register")]
    public function register(RegisterRequest $request, ResponseInterface $response)
    {
        $data = $request->all();

        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        try {

            $token = $this->authService->register($username, $email, $password);

            return $response->json(['token' => $token]);
        } catch (\Exception $e) {
            Log::info("User registration failed for username: {$username}, email: {$email}. Error: " . $e->getMessage());
            return $response->json(['message' => 'Registration failed'])->withStatus(400);
        }
    }

    #[PostMapping(path: "/login")]
    public function login(LoginRequest $request, ResponseInterface $response)
    {
        $data = $request->post();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        try {
            $token = $this->authService->login($email, $password);
            if ($token) {
                return $response->json(['token' => $token]);
            } else {
                return $response->json(['message' => 'Invalid credentials']);
            }
        } catch (\Exception $e) {
            Log::info("Fail to auth" . $e->getMessage());
            return $response->json(['message' => 'Invalid credentials'])->withStatus(401);
        }
    }

    #[PostMapping(path: "/logout")]
    public function logout(RequestInterface $request, ResponseInterface $response)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            $this->authService->logout($token);
        }

        return $response->json(['message' => 'Logged out successfully']);
    }

    #[GetMapping(path: "/me")]
    #[Middleware(AuthMiddleware::class)]
    public function me(RequestInterface $request, ResponseInterface $response)
    {
        /**
         * @var AuthToken
         */
        $auth = $request->getAttribute('auth');

        $user = User::find($auth->user_id);

        return $response->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
