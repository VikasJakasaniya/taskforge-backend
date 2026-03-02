<?php

namespace App\Actions\Auth;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\OtpRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerifyOtpAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OtpRepositoryInterface $otpRepository
    ) {}

    /**
     * Execute the action to verify OTP
     */
    public function execute(string $email, string $otpCode): ?User
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        // Get active OTP
        $otp = $this->otpRepository->getActiveOtpForUser($user->id);

        if (!$otp) {
            return null;
        }

        // Check if expired
        if ($otp->isExpired()) {
            return null;
        }

        // Verify OTP hash
        if (!Hash::check($otpCode, $otp->otp_hash)) {
            return null;
        }

        // Mark OTP as verified
        $otp->markAsVerified();

        return $user;
    }
}
