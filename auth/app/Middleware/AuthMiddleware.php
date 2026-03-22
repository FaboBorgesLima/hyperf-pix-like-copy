<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Log;
use App\Service\AuthService;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    public AuthService $authService;
    public function __construct(protected ContainerInterface $container, protected HttpResponse $response)
    {
        //
    }

    protected function unauthorizedResponse(): ResponseInterface
    {
        return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('Authorization');
        if (!$token) {
            return $this->unauthorizedResponse();
        }
        $token = str_replace('Bearer ', '', $token);

        $authToken = $this->authService->validateToken($token);
        Log::info("Validating token, result: " . ($authToken ? "valid" : "invalid"));

        if (!$authToken) {
            return $this->unauthorizedResponse();
        }
        Log::info("Authenticated request for user_id: {$authToken->user_id}");
        $request = $request->withAttribute('auth', $authToken);

        return $handler->handle($request);
    }
}
