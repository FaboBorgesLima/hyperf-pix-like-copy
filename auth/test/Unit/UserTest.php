<?php

namespace Tests\Unit;

use App\Model\User;
use Hyperf\Context\ApplicationContext;
use Tests\TestCase;

class UserTest extends TestCase
{

    public function testCreateUser(): void
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        $user = $class::create([
            'name' => \Faker\Factory::create()->name(),
            'email' => \Faker\Factory::create()->email(),
            'password' => \Faker\Factory::create()->password(),
        ]);

        $this->assertNotNull($user);
    }

    public function testFindUser(): void
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        $id = $this->createUser()->id;

        $user = $class::find($id);

        $this->assertNotNull($user);
    }

    public function testDeleteUser(): void
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        $id = $this->createUser()->id;

        $user = $class::find($id);

        $this->assertNotNull($user);

        $user->delete();

        $user = $class::find($id);

        $this->assertNull($user);
    }

    public function testUpdateUser(): void
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        $id = $this->createUser()->id;

        $user = $class::find($id);

        $this->assertNotNull($user);

        $newName = \Faker\Factory::create()->name();

        $user->name = $newName;
        $user->save();

        $updatedUser = $class::find($id);

        $this->assertEquals($newName, $updatedUser->name);
    }
}
