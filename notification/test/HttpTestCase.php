<?php

declare(strict_types=1);

namespace HyperfTest;

use Faker\Factory as Faker;
use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\make;

/**
 * Class HttpTestCase.
 * @method get($uri, $data = [], $headers = [])
 * @method post($uri, $data = [], $headers = [])
 * @method json($uri, $data = [], $headers = [])
 * @method file($uri, $data = [], $headers = [])
 * @method request($method, $path, $options = [])
 */
abstract class HttpTestCase extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->client = make(Client::class);
    }

    public function __call($name, $arguments)
    {
        return $this->client->{$name}(...$arguments);
    }

    public function faker()
    {
        return Faker::create();
    }
}
