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

namespace Tests;

use App\Model\User;
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

    public function createUser(): User
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        return $class::create([
            'name' => Factory::create()->name(),
            'email' => Factory::create()->email(),
            'password' => Factory::create()->password(),
        ]);
    }
}
