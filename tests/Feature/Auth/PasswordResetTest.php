<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/password/forgot', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password reset link sent to your email');
    }

    public function test_forgot_password_validates_email_exists(): void
    {
        $response = $this->postJson('/api/password/forgot', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }

    public function test_forgot_password_validates_email_format(): void
    {
        $response = $this->postJson('/api/password/forgot', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();

        // Generate a reset token
        $token = Password::createToken($user);

        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password reset successfully');
    }

    public function test_user_cannot_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid or expired reset token');
    }

    public function test_reset_password_validates_required_fields(): void
    {
        $response = $this->postJson('/api/password/reset', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'token',
                    'email',
                    'password',
                ],
            ]);
    }

    public function test_reset_password_validates_password_length(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'password',
                ],
            ]);
    }

    public function test_reset_password_validates_password_confirmation(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'password',
                ],
            ]);
    }

    public function test_password_can_be_used_to_login_after_reset(): void
    {
        $user = User::factory()->create();
        $oldPassword = 'oldpassword123';

        // Generate token and reset password
        $token = Password::createToken($user);
        $newPassword = 'newpassword123';

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        // Try to login with new password
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $newPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'token',
                ],
            ]);
    }
}
