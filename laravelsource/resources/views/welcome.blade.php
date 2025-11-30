@extends('layouts.app')

@section('title', 'Login - Deadzone Revive')

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

    <!-- Auth Form Container -->
    <div class="form-container p-10 rounded-2xl w-full max-w-lg mx-4 relative z-10" x-data="{ isLogin: true }">
        <!-- Toggle Tabs -->
        <div class="flex mb-8 relative z-10">
            <button type="button" 
                @click="isLogin = true" 
                :class="isLogin ? 'bg-red-600 text-white border-red-600' : 'bg-transparent text-gray-400 border-gray-600 hover:border-red-500/50 hover:text-gray-300'"
                class="flex-1 py-3 px-4 font-bold uppercase tracking-wider text-sm rounded-l-lg border transition-all duration-300">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
            </button>
            <button type="button" 
                @click="isLogin = false" 
                :class="!isLogin ? 'bg-red-600 text-white border-red-600' : 'bg-transparent text-gray-400 border-gray-600 hover:border-red-500/50 hover:text-gray-300'"
                class="flex-1 py-3 px-4 font-bold uppercase tracking-wider text-sm rounded-r-lg border transition-all duration-300">
                <i class="fa-solid fa-user-plus mr-2"></i>Register
            </button>
        </div>

        <div class="text-center mb-10 relative z-10">
            <h1 class="text-4xl font-bold tracking-tight text-white title-underline" x-text="isLogin ? 'WELCOME BACK' : 'CREATE ACCOUNT'">
                WELCOME BACK
            </h1>
        </div>

        @if (session('error'))
            <div class="alert mb-6 p-4 rounded-lg bg-red-900/30 border border-red-500/50 text-red-200 text-sm backdrop-blur-sm">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if (session('status'))
            <div class="alert mb-6 p-4 rounded-lg bg-green-900/30 border border-green-500/50 text-green-200 text-sm backdrop-blur-sm">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('status') }}
            </div>
        @endif

        <!-- Login Form -->
        <form x-show="isLogin" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" id="login-form" action="{{ route('login.post') }}" method="POST" class="space-y-6 relative z-10">
            @csrf

            <!-- Username -->
            <div class="group">
                <label for="login-username" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-user text-red-500"></i>
                    <span>Username</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-user text-gray-500 text-sm"></i>
                    </div>
                    <input type="text" id="login-username" name="username" value="{{ old('username') }}" required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white placeholder-gray-500 focus:outline-none"
                        placeholder="Enter your username">
                    <div class="login-username-info text-xs mt-2 text-gray-400"></div>
                    @error('username')
                        <div class="text-red-400 text-xs mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="group">
                <label for="login-password" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-red-500"></i>
                    <span>Password</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-gray-500 text-sm"></i>
                    </div>
                    <input type="password" id="login-password" name="password" required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white placeholder-gray-500 focus:outline-none"
                        placeholder="Enter your password">
                    <div class="login-password-info text-xs mt-2 text-gray-400"></div>
                    @error('password')
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
                    <div class="text-red-500 text-xs text-center">{{ $message }}</div>
                @enderror
            @endif

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                        class="h-4 w-4 appearance-none rounded border border-gray-600 bg-black
                            checked:bg-red-600 checked:border-red-600
                            focus:ring-2 focus:ring-red-500 focus:outline-none
                            cursor-pointer relative transition-all duration-200
                            before:content-['✓'] before:absolute before:inset-0
                            before:flex before:items-center before:justify-center
                            before:text-white before:text-xs before:font-bold
                            before:opacity-0 checked:before:opacity-100 checked:before:scale-100
                            before:scale-0 before:transition-all before:duration-200">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-300">Remember me</label>
                </div>
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-red-500 hover:text-red-400">Forgot password?</a>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" id="login-button" class="btn-primary w-full flex items-center justify-center gap-3 py-4 px-6 rounded-lg font-bold text-base text-white uppercase tracking-wider relative z-10">
                    <i class="fa-solid fa-right-to-bracket text-lg"></i>
                    <span>Login</span>
                </button>
                <div class="login-info text-xs mt-2 text-gray-400 text-center"></div>
                @error('login')
                    <div class="text-red-400 text-xs mt-2 text-center flex items-center justify-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </div>
                @enderror
            </div>
        </form>

        <!-- Register Form -->
        <form x-show="!isLogin" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" id="register-form" action="{{ route('register.post') }}" method="POST" class="space-y-6 relative z-10" style="display: none;">
            @csrf

            <!-- Username -->
            <div class="group">
                <label for="register-username" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-user text-red-500"></i>
                    <span>Username</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-user text-gray-500 text-sm"></i>
                    </div>
                    <input type="text" id="register-username" name="username" value="{{ old('username') }}" required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white placeholder-gray-500 focus:outline-none"
                        placeholder="Choose a username">
                    <div class="register-username-info text-xs mt-2 text-gray-400"></div>
                    @error('username')
                        <div class="text-red-400 text-xs mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="group">
                <label for="register-email" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-red-500"></i>
                    <span>Email Address</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-envelope text-gray-500 text-sm"></i>
                    </div>
                    <input type="email" id="register-email" name="email" value="{{ old('email') }}" required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white placeholder-gray-500 focus:outline-none"
                        placeholder="your-email@example.com">
                    <div class="register-email-info text-xs mt-2 text-gray-400"></div>
                    @error('email')
                        <div class="text-red-400 text-xs mt-2 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="group">
                <label for="register-password" class="block text-sm font-semibold mb-2 text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-red-500"></i>
                    <span>Password</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-gray-500 text-sm"></i>
                    </div>
                    <input type="password" id="register-password" name="password" required
                        class="input-field w-full pl-12 pr-4 py-3.5 rounded-lg text-white placeholder-gray-500 focus:outline-none"
                        placeholder="Choose a password">
                    <div class="register-password-info text-xs mt-2 text-gray-400"></div>
                    @error('password')
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
                    <div class="text-red-500 text-xs text-center">{{ $message }}</div>
                @enderror
            @endif

            <!-- Submit Button -->
            <div>
                <button type="submit" id="register-button" class="btn-primary w-full flex items-center justify-center gap-3 py-4 px-6 rounded-lg font-bold text-base text-white uppercase tracking-wider relative z-10">
                    <i class="fa-solid fa-user-plus text-lg"></i>
                    <span>Create Account</span>
                </button>
                <div class="register-info text-xs mt-2 text-gray-400 text-center"></div>
                @error('register')
                    <div class="text-red-400 text-xs mt-2 text-center flex items-center justify-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                    </div>
                @enderror
            </div>
        </form>

        <!-- Social Login Divider -->
        <div class="mt-8 relative z-10">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-700/50"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-4 bg-black/50 text-gray-400 uppercase tracking-wider font-semibold">
                        <i class="fa-solid fa-bolt mr-1 text-red-500"></i>Quick Access
                    </span>
                </div>
            </div>
        </div>

        <!-- Social Buttons -->
        <div class="grid grid-cols-3 mt-6 gap-3 relative z-10">
            @if(isset($maintenanceMode) && $maintenanceMode)
                <button disabled class="social-btn flex items-center justify-center gap-2 bg-[#5865F2] opacity-50 cursor-not-allowed text-white font-semibold py-3.5 px-4 rounded-lg">
                    <i class="fab fa-discord text-lg"></i>
                </button>
                <button disabled class="social-btn flex items-center justify-center gap-2 bg-transparent opacity-50 cursor-not-allowed text-white font-semibold py-3.5 px-4 rounded-lg border border-gray-600">
                    <i class="fab fa-x-twitter text-lg"></i>
                </button>
                <button disabled class="social-btn flex items-center justify-center gap-2 bg-white opacity-50 cursor-not-allowed text-black font-semibold py-3.5 px-4 rounded-lg">
                    <i class="fab fa-github text-lg"></i>
                </button>
            @else
                <a href="{{ route('auth.social', 'discord') }}" class="social-btn flex items-center justify-center gap-2 bg-[#5865F2] hover:bg-[#4752C4] text-white font-semibold py-3.5 px-4 rounded-lg relative z-10" title="Login with Discord">
                    <i class="fab fa-discord text-xl"></i>
                </a>
                <a href="{{ route('auth.social', 'twitter') }}" class="social-btn flex items-center justify-center gap-2 bg-black hover:bg-gray-900 text-white font-semibold py-3.5 px-4 rounded-lg border border-gray-600 hover:border-gray-500 relative z-10" title="Login with Twitter">
                    <i class="fab fa-x-twitter text-xl"></i>
                </a>
                <a href="{{ route('auth.social', 'github') }}" class="social-btn flex items-center justify-center gap-2 bg-white hover:bg-gray-200 text-black font-semibold py-3.5 px-4 rounded-lg relative z-10" title="Login with GitHub">
                    <i class="fab fa-github text-xl"></i>
                </a>
            @endif
        </div>

        <!-- Green Spirit Account -->
        <div class="mt-5 relative z-10">
            <button disabled class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-green-700 to-green-600 text-white font-bold py-3.5 px-6 rounded-lg cursor-not-allowed opacity-40 relative overflow-hidden">
                <img src="{{ asset('assets/images/greenspirits.svg') }}" alt="Green Spirit Icon" class="h-6 w-6">
                <span>Spirit Account</span>
                <span class="text-xs bg-white/20 px-2 py-1 rounded">SOON</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/login.js') }}"></script>
@endpush