<?php

namespace Tests;

use App\Model\User;
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
    public function faker(): \Faker\Generator
    {
        return \Faker\Factory::create();
    }
    public function createUser(): User
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        return $class::create([
            'name' => \Faker\Factory::create()->name(),
            'email' => \Faker\Factory::create()->email(),
            'password' => \Faker\Factory::create()->password(),
        ]);
    }
}
