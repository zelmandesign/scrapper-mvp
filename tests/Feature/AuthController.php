<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthController extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $res = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $res->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_failure_returns_401(): void
    {
        $res = $this->postJson('/api/login', [
            'email' => 'nope@example.com',
            'password' => 'wrong',
        ]);

        $res->assertStatus(401);
    }

    public function test_user_requires_auth(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_user_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('t')->plainTextToken;

        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $res->assertOk()->assertJsonFragment(['email' => $user->email]);
    }

    public function test_logout_returns_ok(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out']);
    }
}
