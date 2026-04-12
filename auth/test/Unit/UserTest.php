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

namespace Tests\Unit;

use App\Model\User;
use Faker\Factory;
use Hyperf\Context\ApplicationContext;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $class = ApplicationContext::getContainer()->get(User::class);

        $user = $class::create([
            'name' => Factory::create()->name(),
            'email' => Factory::create()->email(),
            'password' => Factory::create()->password(),
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

        $newName = Factory::create()->name();

        $user->name = $newName;
        $user->save();

        $updatedUser = $class::find($id);

        $this->assertEquals($newName, $updatedUser->name);
    }
}
