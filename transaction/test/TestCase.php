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

namespace HyperfTest;

use Faker\Factory;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Log\LoggerInterface;

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

    public function faker()
    {
        return Factory::create();
    }

    protected function log(...$args)
    {
        return ApplicationContext::getContainer()->get(LoggerInterface::class)->info(...$args);
    }
}
