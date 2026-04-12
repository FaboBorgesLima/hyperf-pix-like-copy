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

namespace App;

use BadMethodCallException;
use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;

class Log
{
    public static function __callStatic($name, $arguments)
    {
        $logger = self::get();

        if (method_exists($logger, $name)) {
            return $logger->{$name}(...$arguments);
        }
        throw new BadMethodCallException("Method {$name} does not exist on Logger");
    }

    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
