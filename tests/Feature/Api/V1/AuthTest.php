<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_and_use_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['token_type', 'token', 'user' => ['id', 'name', 'email']]);

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'staff@example.com');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_api_routes_reject_missing_bearer_token(): void
    {
        $this->getJson('/api/v1/dashboard/summary')
            ->assertUnauthorized();
    }
}
