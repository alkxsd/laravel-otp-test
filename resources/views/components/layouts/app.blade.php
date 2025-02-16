<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>
<body class="antialiased bg-gray-100">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="/" class="text-xl font-bold text-gray-900">
                            {{ config('app.name', 'Laravel') }}
                        </a>
                    </div>

                    @auth
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700 text-sm">Howdy, {{ Auth::user()->name }}!</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-blue-500 hover:text-blue-800">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-auto bg-white shadow-sm">
            <div class="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="text-center text-gray-600">
                    © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
    @livewireScripts
    @livewireScriptConfig
</body>
</html>
