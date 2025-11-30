@extends('layouts.app')

@section('title', 'Verify Email - Deadzone Revive')

@section('content')
<body class="text-white bg-black flex items-center justify-center min-h-screen pt-24">
    <nav class="fixed top-0 w-full z-50 bg-black bg-opacity-90 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img class="h-24" src="https://deadzonegame.net/assets/img/logo.png" alt="Deadzone Revive Logo">
                    </div>
                </div>
                <div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-400 font-medium text-sm transition-all">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <div class="p-8 rounded-xl w-full max-w-lg mx-4 border border-gray-800 bg-black/80 backdrop-blur-sm">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-white">Verify Your Email Address</h1>
            <div class="w-16 h-1 bg-red-600 mx-auto mt-2 rounded-full"></div>
        </div>
        <p class="text-center text-gray-300 mb-6">
            A 6-digit verification code has been sent to your email address. Please check your inbox and enter the code below.
        </p>

        @if (session('message'))
            <div class="mb-4 p-3 rounded-lg bg-green-900/50 border border-green-500 text-green-200 text-sm text-center">
                {{ session('message') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-900/50 border border-red-500 text-red-200 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('verification.verify-code') }}" class="mb-4">
            @csrf
            <div class="mb-4">
                <label for="code" class="block text-sm font-medium mb-2 text-gray-300">Verification Code</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-black border border-gray-700 text-white text-center text-2xl tracking-widest placeholder-gray-500 focus:outline-none focus:border-red-500"
                    style="letter-spacing: 0.5em;"
                >
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 py-3 px-4 rounded-lg font-medium text-sm transition-all">
                Verify Email
            </button>
        </form>

        <form method="POST" action="{{ route('verification.resend-code') }}" class="mb-4">
            @csrf
            <button type="submit" class="w-full bg-transparent hover:bg-gray-900 text-gray-300 font-medium py-3 px-4 rounded-lg border border-gray-700 transition-all">
                Resend Verification Code
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full bg-transparent hover:bg-gray-900 text-white font-medium py-3 px-4 rounded-lg border border-gray-600 transition-all">
                Back to Login
            </button>
        </form>
    </div>
</body>
@endsection
