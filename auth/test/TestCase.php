<?php

namespace Tests;

use App\Model\User;
use Hyperf\Context\ApplicationContext;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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
