<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Title('Welcome')]
class extends Component
{
    public string $name;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Welcome back, {{ $name }}
                </h1>
                <p class="mt-4 text-gray-600">
                    You have successfully verified your identity.
                </p>
            </div>
        </div>
    </div>
</div>
