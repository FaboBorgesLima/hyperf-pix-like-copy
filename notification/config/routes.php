<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/health', 'App\Controller\IndexController@health');
