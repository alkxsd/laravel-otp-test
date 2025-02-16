<?php
use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;
use App\Services\OTPService;
use App\DataTransferObjects\OtpDTO;

new
#[Title('OTP Verification')]
class extends Component {
    public array $digits = ['', '', '', '', '', ''];
    public string $type = 'email';
    public ?string $error = null;
    public bool $success = false;
    public bool $isLoading = false;
    public bool $showResend = false;
    public bool $resending = false;

    public bool $isRateLimited = false;
    public ?int $rateLimitExpiresIn = null;

    public ?string $successMessage = '';

    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 30;


    public function mount(string $type = 'email'): void
    {
        if (!session()->get('requires_otp', false) && !App::environment('testing')) {
            $this->redirect(route('welcome'));
            return;
        }

        $this->type = $type;
        $this->checkRateLimit();
    }

    public function handleInput($index): void
    {
        if ($this->error && !$this->isLoading) {
            $this->clearDigits();
            $this->error = null;
        }
    }

    public function updatedDigits($value, $key): void
    {
        if ($this->isRateLimited) {
            $this->checkRateLimit();
            return;
        }

        if (is_numeric($value) && strlen($value) === 1 && $this->isComplete()) {
            $this->dispatch('remove-focus');
            $this->verifyOTP();
        }
    }

    public function paste($clipboardData, $targetIndex = 0): void
    {
        if ($this->isRateLimited) {
            return;
        }

        $code = preg_replace('/[^0-9]/', '', $clipboardData);
        $codeLength = min(strlen($code), 6 - $targetIndex);
        $code = substr($code, 0, $codeLength);

        if ($codeLength > 0) {
            $digits = str_split($code);

            for ($i = 0; $i < $codeLength; $i++) {
                $this->digits[$targetIndex + $i] = $digits[$i];
            }
            $this->dispatch('digits-updated', focusIndex: $targetIndex + $codeLength);

            if ($this->isComplete()) {
                $this->verifyOTP();
            }
        }
    }


    private function checkRateLimit(): void
    {
        $key = 'verify_otp:' . auth()->id();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->isRateLimited = true;
            $this->rateLimitExpiresIn = RateLimiter::availableIn($key);
            $this->error = "Too many verification attempts. Please try again in {$this->rateLimitExpiresIn} seconds.";
        } else {
            $this->isRateLimited = false;
            $this->rateLimitExpiresIn = null;
        }
    }

    public function clearDigits(): void
    {
        $this->digits = array_fill(0, 6, '');
        $this->dispatch('digits-cleared');
    }

    public function resendOTP(): void
    {
        $this->resending = true;
        $this->error = null;

        $this->successMessage = null;

        try {
            $otpService = app(OTPService::class);
            $otpService->generateOTP(auth()->user(), $this->type);

            $this->digits = array_fill(0, 6, '');
            $this->showResend = false;
            $this->successMessage = 'A new verification code has been sent to your ' . $this->type;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->resending = false;
        }
    }


    public function verifyOTP(): void
    {
        if (!$this->isComplete() || $this->isRateLimited) {
            return;
        }

        $key = 'verify_otp:' . auth()->id();

        $this->error = null;
        $this->isLoading = true;
        $this->showResend = false;

        try {
            $otpService = app(OTPService::class);
            $otpData = new OtpDTO(
                code: implode('', $this->digits),
                type: $this->type,
                userId: auth()->id()
            );

            $otpService->validateOTP($otpData);

            RateLimiter::clear($key);
            $this->success = true;

            // Clear the OTP requirement from session before redirecting
            session()->forget('requires_otp');
            session()->regenerate();

            $this->dispatch('otp-verified', message: 'OTP verified successfully');
            $this->redirect('/');
        } catch (\Exception $e) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            $this->error = $e->getMessage();
            if ($e->status == 466) {
                $otpService = app(OTPService::class);
                $this->showResend = $otpService->canRequestNewOTP(auth()->user(), $this->type);
            }

            $this->checkRateLimit();
            $this->dispatch('otp-error');
        } finally {
            $this->isLoading = false;
        }
    }

    private function isComplete(): bool
    {
        return count(array_filter($this->digits, fn($digit) => strlen($digit) === 1)) === 6;
    }
}; ?>

<div class="w-full max-w-sm mx-auto"
    x-data="{
        currentIndex: 0,
        init() {
            this.$nextTick(() => {
                this.focusFirst();
            });

            $wire.on('digits-updated', ({focusIndex}) => {
                this.$nextTick(() => {
                    console.log('FIOC', focusIndex);
                    if (focusIndex <= 5) {
                        console.log('FOCUSSING', focusIndex);
                        this.$refs[`digit${focusIndex}`]?.focus();
                        this.$refs[`digit${focusIndex}`]?.select();
                    } else {
                        this.$refs[`digit0`]?.blur();
                    }
                });
            });

            $wire.on('digits-cleared', () => {
                console.log('FOCUS FIRST FOO');
                this.focusFirst();
            });

            $wire.on('otp-error', () => {
                console.log('CURRENT', this.currentIndex);
                this.$refs[`digit5`]?.blur();
            });
        },
        focusFirst() {
            if (!@js($isRateLimited)) {
                this.currentIndex = 0;
                console.log('FOCUS FIRST');
                this.$refs[`digit0`]?.focus();
                this.$refs[`digit0`]?.select();
            }
        },
        handlePaste(event, index) {
            if (@js($isRateLimited)) {
                return;
            }

            event.preventDefault();
            const text = event.clipboardData.getData('text').trim();

            if (text) {
                if ($wire.error) {
                    $wire.handleInput(index);
                }
                $wire.paste(text, index);
            }
        }
    }"
>

    @if($successMessage)
        <livewire:components.alert-box
            type="success"
            :message="$successMessage"
        />
    @endif

    @if($error)
        <livewire:components.alert-box
            type="error"
            :message="$error"
        />
    @endif

    <div class="mb-4 text-center">
        <h2 class="text-xl font-semibold">Enter Verification Code</h2>
        <p class="text-gray-600 mt-1">Please enter the 6-digit code sent to your {{ $type }}</p>
    </div>

    <div class="flex gap-2 justify-center mb-4">
        @foreach($digits as $index => $digit)
            <input
                type="text"
                maxlength="1"
                inputmode="numeric"
                x-ref="digit{{ $index }}"
                wire:model.live="digits.{{ $index }}"
                x-on:focus="$wire.handleInput({{ $index }})"
                x-on:keydown.backspace="$event.target.value === '' && {{ $index }} > 0 ? $refs[`digit{{ $index - 1 }}`].focus() : null"
                x-on:keydown.arrow-left="$event.preventDefault(); {{ $index }} > 0 ? $refs[`digit{{ $index - 1 }}`].focus() : null"
                x-on:keydown.arrow-right="$event.preventDefault(); {{ $index }} < 5 ? $refs[`digit{{ $index + 1 }}`].focus() : null"
                x-on:input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '');
                        if ($event.target.value && {{ $index }} < 5) $refs[`digit{{ $index + 1 }}`].focus()"
                x-on:paste="handlePaste($event, {{ $index }})"
                @disabled($isRateLimited)
                class="w-12 h-12 text-center text-xl font-semibold border rounded-lg
                    focus:ring-2 focus:ring-blue-500 focus:outline-none
                    transition-colors duration-200
                    {{ $error ? 'border-red-500 bg-red-50' : 'border-gray-300' }}
                    {{ $success ? 'border-green-500 bg-green-50' : '' }}
                    {{ $isRateLimited ? 'opacity-50 cursor-not-allowed' : '' }}"
            >
        @endforeach
    </div>


    @if($showResend && !$isRateLimited)
        <div class="text-center mt-4">
            <button
                wire:click="resendOTP"
                wire:loading.attr="disabled"
                class="text-blue-600 hover:text-blue-800 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $resending ? 'disabled' : '' }}
            >
                <span wire:loading.remove wire:target="resendOTP">
                    Resend OTP
                </span>
                <span wire:loading wire:target="resendOTP">
                    Sending...
                </span>
            </button>
        </div>
    @endif

    @if($isLoading)
        <div class="text-center text-gray-600">
            <div class="inline-flex items-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Verifying...</span>
            </div>
        </div>
    @endif

    @if($success)
        <div class="text-green-500 text-center mb-4">
            <div class="inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Code verified successfully!</span>
            </div>
        </div>
    @endif
</div>
