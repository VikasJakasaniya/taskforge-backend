<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_otp(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/request-otp', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'expires_at',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('otps', [
            'user_id' => User::where('email', 'test@example.com')->first()->id,
        ]);
    }

    public function test_otp_request_is_rate_limited(): void
    {
        Mail::fake();
        $email = 'test@example.com';

        // Make maximum allowed requests
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/auth/request-otp', ['email' => $email]);
        }

        // Next request should be rate limited
        $response = $this->postJson('/api/auth/request-otp', ['email' => $email]);
        $response->assertStatus(429);
    }

    public function test_user_can_verify_valid_otp(): void
    {
        Mail::fake();
        $email = 'test@example.com';

        // Create user and OTP
        $user = User::factory()->create(['email' => $email]);
        $otpCode = '123456';
        Otp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => $otpCode,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'email'],
            ]);
    }

    public function test_user_cannot_verify_expired_otp(): void
    {
        $email = 'test@example.com';
        $user = User::factory()->create(['email' => $email]);
        $otpCode = '123456';

        Otp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otpCode),
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => $otpCode,
        ]);

        $response->assertStatus(401);
    }

    public function test_otp_is_single_use(): void
    {
        Mail::fake();
        $email = 'test@example.com';
        $user = User::factory()->create(['email' => $email]);
        $otpCode = '123456';

        Otp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(5),
        ]);

        // First verification
        $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => $otpCode,
        ])->assertStatus(200);

        // Second verification should fail
        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => $email,
            'otp' => $otpCode,
        ]);

        $response->assertStatus(401);
    }
}
