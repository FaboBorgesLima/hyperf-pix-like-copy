<?php

declare(strict_types=1);

namespace Tests\Feature;

use HyperfTest\HttpTestCase;

class UserControllerTest extends HttpTestCase
{
    // ── helpers ──────────────────────────────────────────────────────────────

    private function register(): array
    {
        return $this->post('/auth/register', [
            'username' => $this->faker()->name(),
            'email'    => $this->faker()->email(),
            'password' => $this->faker()->password(),
        ]);
    }

    private function authHeader(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    private function me(string $token): array
    {
        return $this->get('/auth/me', [], $this->authHeader($token));
    }

    // ── GET /users/{id} ───────────────────────────────────────────────────────

    public function testShowOwnProfile(): void
    {
        $token = $this->register()['token'];
        $me    = $this->me($token);

        $response = $this->get("/users/{$me['id']}", [], $this->authHeader($token));

        $this->assertEquals($me['id'], $response['id']);
        $this->assertEquals($me['name'], $response['name']);
        $this->assertEquals($me['email'], $response['email']);
    }

    public function testShowProfileUnauthenticated(): void
    {
        $token = $this->register()['token'];
        $me    = $this->me($token);

        $response = $this->client->get("/users/{$me['id']}");

        $this->assertEquals('Unauthorized', $response['message']);
    }

    public function testShowOtherUserProfileForbidden(): void
    {
        $token1 = $this->register()['token'];
        $token2 = $this->register()['token'];
        $me2    = $this->me($token2);

        // User 1 tries to view User 2's profile
        $response = $this->get("/users/{$me2['id']}", [], $this->authHeader($token1));

        // You cannot view other user's profile
        $this->assertNull($response);
    }

    // ── PUT /users/{id} ────────────────────────────────────────────────────

    public function testUpdateProfile(): void
    {
        $token   = $this->register()['token'];
        $newName = $this->faker()->name();
        $me      = $this->me($token);


        $response = $this->client->put("/users/{$me['id']}", [
            'name' => $newName,
        ], $this->authHeader($token));

        $this->assertStringContainsString($newName, $response['name']);
    }

    public function testUpdateProfileUnauthenticated(): void
    {
        $token   = $this->register()['token'];
        $newName = $this->faker()->name();
        $me      = $this->me($token);

        $response = $this->client->put("/users/{$me['id']}", ['name' => $newName]);


        $this->assertEquals('Unauthorized', $response['message']);
    }
}
