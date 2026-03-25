<?php

namespace HyperfTest;

use PHPUnit\Framework\TestCase as BaseTestCase;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    protected ContainerInterface $container;

    protected function log(...$args)
    {
        return ApplicationContext::getContainer()->get(\Psr\Log\LoggerInterface::class)->info(...$args);
    }

    public function __construct(string $name)
    {
        $this->container = ApplicationContext::getContainer();
        return parent::__construct($name);
    }
    public function setUp(): void
    {
        $this->container = ApplicationContext::getContainer();
        parent::setUp();
    }
    public function faker()
    {
        return \Faker\Factory::create();
    }
}
