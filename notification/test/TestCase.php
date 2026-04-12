<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ContainerInterface $container;

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

    public function faker(): Generator
    {
        return Factory::create();
    }
}
