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

use App\Constants\AllMapping;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Testing\HttpClient;
use Psr\Log\LoggerInterface;
use Shared\Auth\Contract\TokenVerifierInterface;
use function Hyperf\Support\env;

#[Controller]
class IndexController
{
    #[Inject]
    protected HttpClient $httpClient;

    #[Inject]
    protected TokenVerifierInterface $tokenVerifier;

    #[Inject]
    protected LoggerInterface $logger;

    #[RequestMapping(path: '/auth/{path:.+}', methods: AllMapping::METHODS)]
    public function index(RequestInterface $requestInterface, ResponseInterface $responseInterface)
    {
        $serviceUrl = \sprintf(
            '%s:%d',
            env('AUTH_HOST', 'http://auth'),
            env('AUTH_PORT', 9501)
        );

        return $this->fowardRequest($requestInterface, $responseInterface, $serviceUrl);
    }

    #[RequestMapping(path: '/users/{path:.+}', methods: AllMapping::METHODS)]
    public function users(RequestInterface $requestInterface, ResponseInterface $responseInterface)
    {
        $serviceUrl = \sprintf(
            '%s:%d',
            env('USER_HOST', 'http://auth'),
            env('USER_PORT', 9501)
        );

        return $this->fowardRequest($requestInterface, $responseInterface, $serviceUrl);
    }

    #[RequestMapping(path: '/echo-headers', methods: AllMapping::METHODS)]
    public function echoHeaders(RequestInterface $requestInterface, ResponseInterface $responseInterface)
    {
        $headers = $requestInterface->getHeaders();

        $this->logger->info('Received request for /echo-headers', [
            'method' => $requestInterface->getMethod(),
            'uri' => $requestInterface->getUri()->getPath(),
            'headers' => $requestInterface->getHeaders(),
        ]);

        $headers = $this->addUserIdToHeaders($requestInterface, $headers);

        return $responseInterface->json([
            'headers' => $headers,
        ]);
    }


    protected function addUserIdToHeaders(RequestInterface $requestInterface, array $headers): array
    {
        $headers['X-User-Id'] = "";

        if ($requestInterface->hasHeader('Authorization')) {
            $token = $requestInterface->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $token);
            try {
                $payload = $this->tokenVerifier->decode($token);
                $headers['X-User-Id'] = $payload ? $payload->user_id : "";
            } catch (\Throwable $e) {
                $this->logger->warning('Token verification failed', ['error' => $e->getMessage()]);
                $headers['X-User-Id'] = "";
            }
        }


        return $headers;
    }

    protected function fowardRequest(
        RequestInterface $requestInterface,
        ResponseInterface $responseInterface,
        string $serviceUrl
    ) {
        $uri = $requestInterface->getUri()->getPath();
        $method = $requestInterface->getMethod();
        $body = $requestInterface->getBody()->getContents();
        $headers = $requestInterface->getHeaders();

        $this->logger->info('Forwarded request', [
            'method' => $method,
            'uri' => $uri,
            'serviceUrl' => $serviceUrl,
            'headers' => $headers,
            'body' => $body,
        ]);

        $headers = $this->addUserIdToHeaders($requestInterface, $headers);

        $response = $this->httpClient->client()->request($method, $serviceUrl . $uri, [
            'headers' => $headers,
            'body' => $body,
        ]);

        $responseBody = $response->getBody()->getContents();
        $this->logger->info('Received response from service', [
            'status' => $response->getStatusCode(),
            'body' => $responseBody,
        ]);

        return $responseInterface->json(
            json_decode($responseBody, true)
        )->withStatus($response->getStatusCode());
    }
}
