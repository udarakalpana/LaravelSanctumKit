<?php

namespace Tests\Feature\Auth;

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_sent_on_registration(): void
    {
        Mail::fake();
        Event::fake();

        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        Event::assertDispatched(UserRegistered::class);
        // Notification will be sent via event listener
    }

    public function test_user_can_verify_email_with_valid_link(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Email verified successfully');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_cannot_verify_email_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => 'invalid-hash',
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_cannot_verify_email_with_tampered_url(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->getJson("/api/email/verify/{$user->id}/invalid-hash");

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_resend_verification_email(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/email/resend', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Verification email sent');
    }

    public function test_user_cannot_resend_if_already_verified(): void
    {
        $user = User::factory()->create(); // Already verified by default
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/email/resend', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Email already verified');
    }

    public function test_resend_requires_authentication(): void
    {
        $response = $this->postJson('/api/email/resend', []);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_already_verified_email_returns_message(): void
    {
        $user = User::factory()->create(); // Already verified

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Email already verified');
    }
}
