<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RequestOtpAction;
use App\Actions\Auth\VerifyOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private RequestOtpAction $requestOtpAction,
        private VerifyOtpAction $verifyOtpAction
    ) {}

    /**
     * Request OTP
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];

        // Rate limiting
        $key = 'otp-request:' . $email;
        $maxAttempts = config('app.otp_request_limit', 3);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many OTP requests. Please try again in {$seconds} seconds."
            ], 429);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        try {
            $result = $this->requestOtpAction->execute($email);

            return response()->json([
                'message' => 'OTP sent successfully',
                'expires_at' => $result['expires_at']->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('OTP generation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $otp = $validated['otp'];

        // Rate limiting
        $key = 'otp-verify:' . $email;
        $maxAttempts = config('app.otp_verify_limit', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many verification attempts. Please try again in {$seconds} seconds."
            ], 429);
        }

        $user = $this->verifyOtpAction->execute($email, $otp);

        if (!$user) {
            RateLimiter::hit($key, 300); // 5 minutes
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        // Clear rate limiter on success
        RateLimiter::clear($key);

        // Generate Sanctum token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Authentication successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => auth()->user()
        ]);
    }
}
