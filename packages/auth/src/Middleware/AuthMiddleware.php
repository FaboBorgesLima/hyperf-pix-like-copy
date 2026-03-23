<?php

declare(strict_types=1);

namespace Shared\Auth\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shared\Auth\Contract\TokenVerifierInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class AuthMiddleware implements MiddlewareInterface
{
    private TokenVerifierInterface $tokenVerifier;
    private LoggerInterface $logger;

    public function __construct(
        protected ContainerInterface $container,
        protected HttpResponse $response
    ) {
        $this->tokenVerifier = $container->get(TokenVerifierInterface::class);
        $this->logger = $container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : new NullLogger();
    }

    protected function unauthorizedResponse(): ResponseInterface
    {
        return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!$header) {
            return $this->unauthorizedResponse();
        }

        $token = str_replace('Bearer ', '', $header);

        $authToken = $this->tokenVerifier->decode($token);
        $this->logger->info('Validating token, result: ' . ($authToken ? 'valid' : 'invalid'));

        if (!$authToken || $this->tokenVerifier->isBlacklisted($token)) {
            return $this->unauthorizedResponse();
        }

        $this->logger->info("Authenticated request for user_id: {$authToken->user_id}");
        $request = $request->withAttribute('auth', $authToken);

        return $handler->handle($request);
    }
}
