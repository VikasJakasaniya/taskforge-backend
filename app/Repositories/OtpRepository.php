<?php

namespace App\Repositories;

use App\Contracts\Repositories\OtpRepositoryInterface;
use App\Models\Otp;
use Carbon\Carbon;

class OtpRepository extends BaseRepository implements OtpRepositoryInterface
{
    public function __construct(Otp $model)
    {
        parent::__construct($model);
    }

    public function createForUser(int $userId, string $otpHash, Carbon $expiresAt): Otp
    {
        return $this->create([
            'user_id' => $userId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public function invalidateUserOtps(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('verified_at')
            ->update(['verified_at' => Carbon::now()]);
    }

    public function getActiveOtpForUser(int $userId): ?Otp
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}
