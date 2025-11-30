<!-- Navigation -->
<nav class="fixed top-0 w-full z-50 bg-black/95 backdrop-blur-sm border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="{{ route('login') }}" class="flex-shrink-0">
                <img class="h-16 w-auto" src="https://deadzonegame.net/assets/img/logo.png" alt="Deadzone Revive Logo">
            </a>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('game') }}" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                        <i class="fa-solid fa-gamepad mr-2"></i>Play Game
                    </a>
                @endauth
                <a href="{{ route('forum.index') }}" class="text-white px-4 py-2 text-sm font-medium transition-all rounded-lg bg-red-900/20 border border-red-500/50">
                    <i class="fa-solid fa-comments mr-2"></i>Forum
                </a>
                <a href="https://status.deadzonegame.net/" target="_blank" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                    <i class="fa-solid fa-circle-info mr-2"></i>Status
                </a>
                @auth
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i>Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white px-4 py-2 text-sm font-medium transition-all rounded-lg hover:bg-red-900/20 border border-transparent hover:border-red-500/50">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
