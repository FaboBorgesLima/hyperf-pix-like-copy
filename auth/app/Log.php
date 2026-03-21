<?php


namespace App;

use Hyperf\Context\ApplicationContext;


class Log
{
    public static function __callStatic($name, $arguments)
    {
        $logger = self::get();

        if (method_exists($logger, $name)) {
            return $logger->$name(...$arguments);
        }
        throw new \BadMethodCallException("Method {$name} does not exist on Logger");
    }
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class)->get($name);
    }
}
