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

namespace Tests\Feature;

use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class AuthControllerTest extends HttpTestCase
{
    public function testMe(): void
    {
        $result = $this->client->get('/auth/me');

        $this->assertEquals($result['message'], 'Unauthorized');

        $username = $this->faker()->name();
        $email = $this->faker()->email();
        $password = $this->faker()->password();

        $response = $this->post('/auth/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertNotNull($response['token']);

        $response = $this->get('/auth/me', [], [
            'Authorization' => 'Bearer ' . $response['token'],
        ]);

        $this->assertEquals($response['name'], $username);
    }

    public function testRegister(): void
    {
        $username = $this->faker()->name();
        $email = $this->faker()->email();
        $password = $this->faker()->password();

        $response = $this->post('/auth/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertNotNull($response['token']);

        $response = $this->get('/auth/me', [], [
            'Authorization' => 'Bearer ' . $response['token'],
        ]);

        $this->assertEquals($response['name'], $username);
    }

    public function testLogin(): void
    {
        $username = $this->faker()->name();
        $email = $this->faker()->email();
        $password = $this->faker()->password();

        $response = $this->post('/auth/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertNotNull($response['token']);

        $response = $this->post('/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertNotNull($response['token']);
    }
}
