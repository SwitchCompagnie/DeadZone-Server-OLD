@extends('layouts.app')

@section('title', 'Verify Email - Deadzone Revive')

@section('content')
<div class="flex items-center justify-center min-h-screen pt-24 overflow-hidden relative">
    <div class="fixed inset-0 bg-animated"></div>
    <div class="fixed inset-0 bg-dots"></div>
    <div class="fixed inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/80"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-black/95 backdrop-blur-sm border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="{{ route('welcome') }}" class="flex-shrink-0">
                    <img class="h-16 w-auto" src="https://deadzonegame.net/assets/img/logo.png" alt="Deadzone Revive Logo">
                </a>
                <div class="flex items-center gap-4">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Form Container -->
    <div class="form-container p-10 rounded-2xl w-full max-w-lg mx-4 relative z-10">
        <div class="text-center mb-10 relative z-10">
            <h1 class="text-4xl font-bold tracking-tight text-white title-underline">
                <i class="fa-solid fa-envelope-circle-check mr-2"></i>Verify Email
            </h1>
            <p class="text-gray-400 mt-4 text-sm">A 6-digit verification code has been sent to your email address. Please check your inbox and enter the code below.</p>
        </div>

        @if (session('message'))
            <div class="alert mb-6 p-4 rounded-lg bg-green-900/30 border border-green-500/50 text-green-200 text-sm backdrop-blur-sm">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('message') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert mb-6 p-4 rounded-lg bg-red-900/30 border border-red-500/50 text-red-200 text-sm backdrop-blur-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('verification.verify-code') }}" class="space-y-6 relative z-10">
            @csrf
            
            <!-- Verification Code -->
            <div class="group">
                <label for="code" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-hashtag text-red-500"></i>
                    <span>Verification Code</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-key text-gray-500 text-sm"></i>
                    </div>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder="000000"
                        required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white text-center text-2xl tracking-widest placeholder-gray-500 focus:outline-none"
                        style="letter-spacing: 0.5em;"
                    >
                </div>
            </div>
            
            <!-- Submit Button -->
            <div>
                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-3 py-4 px-6 rounded-lg font-bold text-base text-white uppercase tracking-wider relative z-10">
                    <i class="fa-solid fa-check-circle text-lg"></i>
                    <span>Verify Email</span>
                </button>
            </div>
        </form>

        <!-- Resend Code -->
        <form method="POST" action="{{ route('verification.resend-code') }}" class="mt-4 relative z-10">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-transparent hover:bg-gray-900/50 text-gray-300 hover:text-white font-medium py-3 px-4 rounded-lg border border-gray-700 hover:border-red-500/50 transition-all">
                <i class="fa-solid fa-rotate-right"></i>
                <span>Resend Verification Code</span>
            </button>
        </form>
    </div>
</div>
@endsection
