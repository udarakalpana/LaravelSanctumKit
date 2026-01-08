<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'message',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('message', 'User profile retrieved');
    }

    public function test_user_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated');
    }

    public function test_user_profile_with_invalid_token(): void
    {
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_user_profile_contains_correct_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertJsonPath('data.user.name', 'Jane Doe')
            ->assertJsonPath('data.user.email', 'jane@example.com');
    }
}
