<?php

namespace App\Services;

use App\Models\OTP;
use App\Models\User;
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
     *
     * @param OtpDTO $otpData
     * @return boolean
     */
    public function validateOTP(OtpDTO $otpData): bool
    {
        $otp = $this->otp->where('user_id', $otpData->userId)
            ->where('code', $otpData->code)
            ->where('type', $otpData->type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            $message = $this->isOTPExpired($otpData) ?
                'OTP has expired. Click "Resend OTP" to get a new code.' :
                'Invalid OTP code.';

            throw ValidationException::withMessages([
                'code' => [$message],
            ]);
        }

        $otp->update(['verified_at' => now()]);

        return true;
    }

    /**
     * Generating OTP for a certain user
     *
     * @param User $user
     * @param string $type
     * @return OTP
     */
    public function generateOTP(User $user, string $type = 'email'): OTP
    {
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

        return OTP::createForUser($user, $type);
    }

    /**
     * Determine whether the user can request New OTP
     *
     * @param User $user
     * @param string $type
     * @return boolean
     */
    public function canRequestNewOTP(User $user, string $type = 'email'): bool
    {
        return !Cache::has("otp_generation_{$user->id}_{$type}");
    }

    /**
     * Checks if OTP already expired
     *
     * @param OtpDTO $otpData
     * @return boolean
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
     *
     * @return void
     */
    public function cleanupExpiredOTPs(): void
    {
        $this->otp->where('expires_at', '<', now())->delete();
    }
}
