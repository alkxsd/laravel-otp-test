<?php
use Livewire\Volt\Component;

new class extends Component {
    public string $type = 'success';
    public string $message = '';

    public function mount(string $type = 'success', string $message = ''): void
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function with(): array
    {
        return [
            'bgColor' => $this->type === 'success' ? 'bg-green-50' : 'bg-red-50',
            'textColor' => $this->type === 'success' ? 'text-green-500' : 'text-red-500',
            'icon' => $this->type === 'success'
                ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        ];
    }
}; ?>

<div @class([
    'mb-4 rounded-lg',
    $bgColor,
    $textColor
])>
    <div class="flex flex-row items-center justify-start px-4 py-2 rounded-md">
        <div>
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
            </svg>
        </div>
        <span>{{ $message }}</span>
    </div>
</div>
