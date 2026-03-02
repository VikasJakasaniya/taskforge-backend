<?php

namespace App\Actions\Auth;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\OtpRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RequestOtpAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OtpRepositoryInterface $otpRepository
    ) {}

    /**
     * Execute the action to request OTP
     */
    public function execute(string $email): array
    {
        // Find or create user
        $user = $this->userRepository->firstOrCreate($email, [
            'name' => fake()->name()
        ]);

        // Invalidate previous OTPs
        $this->otpRepository->invalidateUserOtps($user->id);

        // Generate OTP code
        $otpCode = $this->generateOtpCode();
        $expiryMinutes = (int) config('app.otp_expiry_minutes', 5);

        // Store hashed OTP
        $otp = $this->otpRepository->createForUser(
            $user->id,
            Hash::make($otpCode),
            Carbon::now()->addMinutes($expiryMinutes)
        );

        // Send email
        $this->sendOtpEmail($email, $otpCode);

        Log::info("OTP sent to {$email}: {$otpCode}"); // Remove in production

        return [
            'user' => $user,
            'otp_code' => $otpCode,
            'expires_at' => $otp->expires_at,
        ];
    }

    /**
     * Generate OTP code
     */
    private function generateOtpCode(): string
    {
        $length = config('app.otp_length', 6);
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP email
     */
    private function sendOtpEmail(string $email, string $otpCode): void
    {
        Mail::raw(
            "Your TaskForge OTP is: {$otpCode}\n\nThis code expires in 5 minutes.",
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your TaskForge OTP');
            }
        );
    }
}
