<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $tokenModel = $user->createToken('test-token');
        $token = $tokenModel->plainTextToken;

        // Verify token works before logout
        $this->getJson('/api/user', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        // Logout
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged out successfully');

        // Verify token is revoked - reload from database
        $userReloaded = User::find($user->id);
        $this->assertEquals(0, $userReloaded->tokens()->count(), 'Token should be deleted after logout');
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/logout', []);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_logout_with_invalid_token(): void
    {
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}
