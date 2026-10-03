<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'nesrine@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'nesrine@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token', 'token_type']]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'nesrine@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'nesrine@example.com',
            'password' => 'totally-wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fails_cleanly_when_email_does_not_exist(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'ghost@example.com',
            'password' => 'anything',
        ]);

        $response->assertStatus(401);
    }
}
