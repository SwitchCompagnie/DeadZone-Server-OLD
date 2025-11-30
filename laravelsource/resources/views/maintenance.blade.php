@extends('layouts.app')

@section('title', 'Scheduled Maintenance - Deadzone Revive')

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .pulse-animation {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush

@section('content')
<body class="text-white bg-black flex items-center justify-center min-h-screen">
    <div class="text-center max-w-2xl mx-4">
        <div class="mb-8">
            <img class="h-32 mx-auto" src="https://deadzonegame.net/assets/img/logo.png" alt="Deadzone Revive Logo">
        </div>

        <div class="bg-black/80 backdrop-blur-sm border border-red-900 rounded-xl p-8 shadow-2xl">
            <div class="mb-6">
                <i class="fa-solid fa-wrench text-6xl text-red-600 pulse-animation"></i>
            </div>

            <h1 class="text-4xl font-bold mb-4 text-red-500">Scheduled Maintenance</h1>

            <div class="w-24 h-1 bg-red-600 mx-auto mb-6 rounded-full"></div>

            <div class="space-y-4 text-gray-300">
                <p class="text-xl">
                    {!! nl2br(e($message)) !!}
                </p>

                @if($eta && $eta !== '00:00')
                <div class="mt-6 p-4 bg-red-900/30 border border-red-800 rounded-lg">
                    <p class="text-sm text-gray-400 mb-1">Estimated Completion Time</p>
                    <p class="text-3xl font-bold text-red-500">{{ $eta }}</p>
                    <p class="text-xs text-gray-500 mt-1">Local Time</p>
                </div>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-gray-800">
                <p class="text-sm text-gray-400">
                    Follow us for updates:
                </p>
                <div class="flex justify-center gap-4 mt-3">
                    <a href="https://discord.gg/7EyxwYEush" target="_blank" class="text-gray-400 hover:text-[#5865F2] transition-colors">
                        <i class="fab fa-discord text-2xl"></i>
                    </a>
                    <a href="https://deadzonegame.net" target="_blank" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-globe text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 text-gray-500 text-sm">
            <p>© 2025 Ruby Realms Studio - V0.2</p>
        </div>
    </div>
</body>
@endsection
