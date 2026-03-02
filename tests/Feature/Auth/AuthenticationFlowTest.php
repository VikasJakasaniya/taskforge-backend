<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_authentication_flow(): void
    {
        Mail::fake();

        // Step 1: Request OTP
        $response = $this->postJson('/api/auth/request-otp', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // Step 2: Get the OTP (in real world, user gets it from email)
        $otp = Otp::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->first();
        $this->assertNotNull($otp);

        // Simulate OTP code (in tests we need to create it with known value)
        $otpCode = '123456';
        $otp->update(['otp_hash' => Hash::make($otpCode)]);

        // Step 3: Verify OTP
        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'test@example.com',
            'otp' => $otpCode,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $token = $response->json('token');

        // Step 4: Use token to access protected route
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'test@example.com');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $response = $this->postJson('/api/auth/request-otp', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_invalid_otp_format_is_rejected(): void
    {
        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'test@example.com',
            'otp' => '12345', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);
    }

    public function test_user_cannot_verify_wrong_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Otp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'test@example.com',
            'otp' => '654321', // Wrong OTP
        ]);

        $response->assertStatus(401);
    }

    public function test_verify_otp_rate_limiting_works(): void
    {
        $email = 'test@example.com';
        $user = User::factory()->create(['email' => $email]);

        Otp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        // Make maximum allowed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/verify-otp', [
                'email' => $email,
                'otp' => '000000'
            ]);
        }

        // Next request should be rate limited
        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => '000000'
        ]);

        $response->assertStatus(429);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }
}
