<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_account(): void
    {
        Akun::factory()->admin()->create(['username' => 'admin']);

        $this->postJson('/api/auth/login', ['username' => 'admin', 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.akun.role', 'admin')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'akun' => ['id', 'username', 'role', 'nama']]]);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        Akun::factory()->create(['username' => 'dimas']);

        $this->postJson('/api/auth/login', ['username' => 'dimas', 'password' => 'salah'])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_validation_errors_use_the_api_envelope(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['data' => ['username', 'password']]);
    }

    public function test_me_and_logout(): void
    {
        $akun = Akun::factory()->create();
        $token = $akun->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.username', $akun->username);

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_route_without_token_returns_401(): void
    {
        $this->getJson('/api/admin/leads')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}
