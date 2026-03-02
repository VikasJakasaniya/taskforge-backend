<?php

namespace App\Contracts\Repositories;

use App\Models\Otp;
use Carbon\Carbon;

interface OtpRepositoryInterface extends RepositoryInterface
{
    /**
     * Create an OTP for a user
     */
    public function createForUser(int $userId, string $otpHash, Carbon $expiresAt): Otp;

    /**
     * Invalidate all active OTPs for a user
     */
    public function invalidateUserOtps(int $userId): int;

    /**
     * Get active OTP for a user
     */
    public function getActiveOtpForUser(int $userId): ?Otp;
}
