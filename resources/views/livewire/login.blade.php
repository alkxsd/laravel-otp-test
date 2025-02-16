<?php

use App\Livewire\Forms\LoginForm;
use App\Services\OTPService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Login')]
class extends Component
{
    public LoginForm $form;

    public string $testEmail = 'aleks@example.com';
    public string $testPassword = 'password';

    public function login(): void
    {
        try {
            if ($this->form->authenticate(app(OTPService::class))) {
                $this->redirect('/verify-otp');
            } else {
                $this->addError('form.email', 'Invalid email or password.');
            }
        } catch (\Exception $e) {
            $this->addError('form.email', $e->getMessage());
        }
    }
}; ?>

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="px-4 py-8 bg-white shadow sm:rounded-lg sm:px-10">
        <div class="mb-6 sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="text-2xl font-bold leading-9 tracking-tight text-center text-gray-900">
                Sign in to your account
            </h2>
        </div>

        <form wire:submit="login" class="space-y-6">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">
                    Email address
                </label>
                <div class="mt-2">
                    <input
                        wire:model="form.email"
                        id="email"
                        type="text"
                        autocomplete="email"
                        required
                        class="block w-full rounded-md border-0 p-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    >
                    @error('form.email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                    Password
                </label>
                <div class="mt-2">
                    <input
                        wire:model="form.password"
                        id="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="block w-full rounded-md border-0 p-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    >
                    @error('form.password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        wire:model="form.remember"
                        id="remember"
                        type="checkbox"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-600"
                    >
                    <label for="remember" class="block ml-3 text-sm leading-6 text-gray-900">
                        Remember me
                    </label>
                </div>
            </div>

            <div>
                <button
                    type="submit"
                    class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>
                        Sign in
                    </span>
                    <div wire:loading class="flex">
                        <div class="flex flex-row items-center">
                            <svg class="w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Logging in...
                        </div>
                    </div>
                </button>
            </div>
        </form>
    </div>
</div>
