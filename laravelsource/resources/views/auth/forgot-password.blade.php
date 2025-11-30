@extends('layouts.app')

@section('title', 'Forgot Password - Deadzone Revive')

@push('head-scripts')
    @if (env('TURNSTILE_ENABLED', false))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
@endpush

@section('content')
<div class="flex items-center justify-center min-h-screen pt-24 overflow-hidden relative">
    <div class="fixed inset-0 bg-animated"></div>
    <div class="fixed inset-0 bg-dots"></div>
    <div class="fixed inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/80"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-black/95 backdrop-blur-sm border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="{{ route('login') }}" class="flex-shrink-0">
                    <img class="h-16 w-auto" src="https://deadzonegame.net/assets/img/logo.png" alt="Deadzone Revive Logo">
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('forum.index') }}" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                        <i class="fa-solid fa-comments mr-2"></i>Forum
                    </a>
                    <a href="https://status.deadzonegame.net/" target="_blank" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                        <i class="fa-solid fa-circle-info mr-2"></i>Status
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Form Container -->
    <div class="form-container p-10 rounded-2xl w-full max-w-lg mx-4 relative z-10">
        <div class="text-center mb-10 relative z-10">
            <h1 class="text-4xl font-bold tracking-tight text-white title-underline">
                <i class="fa-solid fa-key mr-2"></i>Reset Password
            </h1>
            <p class="text-gray-400 mt-4 text-sm">Enter your email address and we will send you a link to reset your password.</p>
        </div>

        @if (session('status'))
            <div class="alert mb-6 p-4 rounded-lg bg-green-900/30 border border-green-500/50 text-green-200 text-sm backdrop-blur-sm">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6 relative z-10">
            @csrf
            
            <!-- Email -->
            <div class="group">
                <label for="email" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-red-500"></i>
                    <span>Email Address</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-envelope text-gray-500 text-sm"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white placeholder-gray-500 focus:outline-none"
                        placeholder="your-email@example.com">
                    @error('email')
                        <div class="text-red-400 text-xs mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
            
            <!-- Turnstile -->
            @if (env('TURNSTILE_ENABLED', false))
                <div class="flex justify-center">
                    <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITEKEY') }}"></div>
                </div>
                @error('captcha')
                    <div class="text-red-400 text-xs text-center flex items-center justify-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </div>
                @enderror
            @endif
            
            <!-- Submit Button -->
            <div>
                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-3 py-4 px-6 rounded-lg font-bold text-base text-white uppercase tracking-wider relative z-10">
                    <i class="fa-solid fa-paper-plane text-lg"></i>
                    <span>Send Reset Link</span>
                </button>
            </div>
        </form>
        
        <!-- Back to Login -->
        <div class="mt-6 text-center relative z-10">
            <a href="{{ route('welcome') }}" class="font-medium text-red-500 hover:text-red-400 flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Login</span>
            </a>
        </div>
    </div>
</div>
@endsection
