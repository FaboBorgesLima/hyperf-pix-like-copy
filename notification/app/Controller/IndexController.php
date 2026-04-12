<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller]
class IndexController
{
    #[GetMapping(path: '/health')]
    public function health(ResponseInterface $response)
    {
        return $response->json(['status' => 'ok', 'service' => 'notification']);
    }
}
