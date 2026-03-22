<?php

namespace Tests\Unit;


use App\Service\AuthService;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{

    public function testRegister(): void
    {
        $authService = $this->container->get(AuthService::class);

        $username = $this->faker()->name();
        $email = $this->faker()->email();
        $password = $this->faker()->password();

        $token = $authService->register($username, $email, $password);
        $this->assertNotNull($token);
    }
    public function testRegisterDuplicateEmail(): void
    {
        $authService = $this->container->get(AuthService::class);

        $username1 = $this->faker()->name();
        $email = $this->faker()->email();
        $password1 = $this->faker()->password();

        $username2 = $this->faker()->name();
        $password2 = $this->faker()->password();

        // Register first user
        $token1 = $authService->register($username1, $email, $password1);
        $this->assertNotNull($token1);

        // Attempt to register second user with same email
        try {
            $authService->register($username2, $email, $password2);
            $this->fail("Expected exception not thrown for duplicate email");
        } catch (\Exception $e) {
            // Expected exception for duplicate email
            $this->assertTrue(true);
        }
    }
    public function testLogin(): void
    {
        $authService = $this->container->get(AuthService::class);

        $username = $this->faker()->name();
        $email = $this->faker()->email();
        $password = $this->faker()->password();

        // Register a new user
        $token = $authService->register($username, $email, $password);
        $this->assertNotNull($token);

        // Attempt to login with correct credentials
        $loginToken = $authService->login($email, $password);
        $this->assertNotNull($loginToken);

        // Attempt to login with incorrect password
        $invalidLoginToken = $authService->login($email, 'wrongpassword');
        $this->assertNull($invalidLoginToken);
    }

    public function testLogout(): void
    {
        $authService = $this->container->get(AuthService::class);

        $username = $this->faker()->name();
        $email = $this->faker()->email();
        $password = $this->faker()->password();

        // Register and login a new user
        $token = $authService->register($username, $email, $password);
        $this->assertNotNull($token);

        // Logout the user
        $authService->logout($token);

        // Attempt to get user from token after logout
        $user = $authService->getUserFromToken($token);
        $this->assertNull($user);
    }
}
