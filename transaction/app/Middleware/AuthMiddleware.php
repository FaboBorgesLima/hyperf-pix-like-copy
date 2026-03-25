<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected HttpResponse $response
    ) {
        //
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId = $request->getHeaderLine('X-User-Id');

        if (empty($userId)) {
            return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
        }

        return $handler->handle($request);
    }
}
