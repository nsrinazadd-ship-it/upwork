<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Nesrine',
            'last_name' => 'Zadeh',
            'email' => 'nesrine@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'client',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'access_token', 'token_type']]);

        $this->assertDatabaseHas('users', [
            'email' => 'nesrine@example.com',
            'first_name' => 'Nesrine',
            'last_name' => 'Zadeh',
            'role' => 'client',
        ]);
    }

    public function test_registration_fails_when_email_already_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/register', [
            'first_name' => 'Nesrine',
            'last_name' => 'Zadeh',
            'email' => 'taken@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'client',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_when_role_is_invalid(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Nesrine',
            'last_name' => 'Zadeh',
            'email' => 'nesrine@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin', // بس client/freelancer مسموحين بالتسجيل الذاتي
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Nesrine',
            'last_name' => 'Zadeh',
            'email' => 'nesrine@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
            'role' => 'client',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
