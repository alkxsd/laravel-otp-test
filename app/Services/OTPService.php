<?php

namespace App\Services;

use App\Models\OTP;
use App\Models\User;
use App\Notifications\OTPNotification;
use App\DataTransferObjects\OtpDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Service class for handling OTP (One Time Password) operations
 */
class OTPService
{
    public function __construct(
        private OTP $otp
    ) {}

    /**
     * Handle OTP Validation
     */
    public function validateOTP(OtpDTO $otpData): bool
    {
        $otp = $this->otp->where('user_id', $otpData->userId)
            ->where('code', $otpData->code)
            ->where('type', $otpData->type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $otp) {
            $isOTPExpired = $this->isOTPExpired($otpData);
            $message = $isOTPExpired ?
                'OTP has expired. Click "Resend OTP" to get a new code.' :
                'Invalid OTP code.';
            $statusCode = $isOTPExpired ? 466 : 477;
            throw ValidationException::withMessages([
                'code' => [$message],
            ])->status($statusCode);
        }

        $otp->update(['verified_at' => now()]);

        return true;
    }

    /**
     * Generating OTP for a certain user
     */
    public function generateOTP(User $user, string $type = 'email'): OTP
    {
        // Clear all expired otp before creating new
        $this->cleanupExpiredOTPs($user);

        $cacheKey = "otp_generation_{$user->id}_{$type}";

        if (Cache::has($cacheKey)) {
            $timeLeft = Cache::ttl($cacheKey);
            throw ValidationException::withMessages([
                'code' => ["Please wait {$timeLeft} seconds before requesting another OTP."],
            ]);
        }

        Cache::put($cacheKey, true, now()->addSeconds(10));

        // Invalidate any existing unverified OTPs for this user and type
        $this->otp->where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);

        $otp = OTP::createForUser($user, $type);

        try {
            $user->notify(new OTPNotification(
                code: $otp->code,
                expiresInMinutes: 15
            ));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw ValidationException::withMessages([
                'code' => ['Failed to send OTP. Please try again.'],
            ]);
        }

        return $otp;
    }

    /**
     * Determine whether the user can request New OTP
     */
    public function canRequestNewOTP(User $user, string $type = 'email'): bool
    {
        return ! Cache::has("otp_generation_{$user->id}_{$type}");
    }

    /**
     * Checks if OTP already expired
     */
    public function isOTPExpired(OtpDTO $otpData): bool
    {
        return $this->otp->where('user_id', $otpData->userId)
            ->where('code', $otpData->code)
            ->where('type', $otpData->type)
            ->whereNull('verified_at')
            ->where('expires_at', '<=', now())
            ->exists();
    }

    /**
     * Clean up expired otps
     */
    public function cleanupExpiredOTPs(User $user): void
    {
        $this->otp->where('expires_at', '<', now())
            ->where('user_id', $user->id)
            ->delete();
    }
}
