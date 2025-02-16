<?php

namespace App\Livewire\Forms;

use App\Services\OTPService;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;
use Livewire\Attributes\Validate;

class LoginForm extends Form
{
    #[Validate('required|email')]
    public string $email = 'test@example.com';

    #[Validate('required|min:8')]
    public string $password = 'password';

    public bool $remember = false;

    public function authenticate(OTPService $otpService)
    {
        $this->validate();

        if (Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            session()->regenerate();

            try {
                $otp = $otpService->generateOTP(Auth::user(), 'email');
                session()->put('requires_otp', true);

                return true;
            } catch (\Exception $e) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                throw $e;
            }
        }

        return false;
    }
}
