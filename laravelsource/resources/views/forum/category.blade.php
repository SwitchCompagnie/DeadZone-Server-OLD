@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="min-h-screen bg-black">
    @include('partials.forum-nav')

    <!-- Content -->
    <div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10">
                <nav class="text-sm text-gray-400 mb-4">
                    <a href="{{ route('forum.index') }}" class="hover:text-white transition-colors">Forum</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-500">{{ $category->name }}</span>
                </nav>

                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl md:text-4xl font-bold break-words">{{ $category->name }}</h1>
                        @if($category->description)
                            <p class="text-gray-400 mt-3 text-lg">{{ $category->description }}</p>
                        @endif
                    </div>
                    @auth
                        <a href="{{ route('forum.thread.create', $category->slug) }}"
                           class="flex-shrink-0 inline-flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 rounded-lg text-white font-medium transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Thread
                        </a>
                    @else
                        <div class="flex-shrink-0 group relative">
                            <button disabled
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-700 cursor-not-allowed rounded-lg text-gray-400 font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                New Thread
                            </button>
                            <div class="absolute top-full right-0 mt-2 w-64 p-3 bg-gray-800 border border-gray-700 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                                <p class="text-sm text-gray-300 mb-2">Login required to create threads</p>
                                <a href="{{ route('login') }}" class="text-xs text-red-500 hover:text-red-400 font-medium">Sign in now →</a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>

            @guest
                <div class="mb-6 bg-gradient-to-r from-blue-900/20 to-purple-900/20 border border-blue-800/50 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-blue-300 mb-1">Join the Conversation</h4>
                            <p class="text-sm text-gray-400">
                                <a href="{{ route('login') }}" class="text-red-500 hover:text-red-400 font-medium">Sign in</a> to create new threads, reply to posts, and like content.
                            </p>
                        </div>
                    </div>
                </div>
            @endguest

            <div class="bg-gray-900/50 border border-gray-800 rounded-xl overflow-hidden backdrop-blur-sm">
                <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700">
                    <div class="flex items-center text-sm font-semibold text-gray-400 uppercase tracking-wide">
                        <div class="flex-1">Thread</div>
                        <div class="w-24 text-center hidden lg:block">Replies</div>
                        <div class="w-24 text-center hidden lg:block">Likes</div>
                        <div class="w-24 text-center hidden lg:block">Views</div>
                    </div>
                </div>

                <div class="divide-y divide-gray-800/50">
                    @forelse($threads as $thread)
                        <div class="px-6 py-5 hover:bg-gray-800/50 transition-all">
                            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start gap-2 flex-wrap">
                                        @if($thread->is_pinned)
                                            <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L11 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c-.25.78.128 1.626.847 1.916.719.29 1.555-.028 1.805-.808L7.77 11.03 5 10.274zm14.158 3.096a1 1 0 01.684 1.07l-1 6A1 1 0 0117.848 21H2.152a1 1 0 01-.994-.86l-1-6a1 1 0 011.686-.98l1.523 1.524 4.674-8.011a1 1 0 011.697 0l4.674 8.011 1.523-1.524a1 1 0 011.002-.094z"/>
                                            </svg>
                                        @endif
                                        @if($thread->is_locked)
                                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                        <a href="{{ route('forum.thread.show', $thread->slug) }}"
                                           class="text-lg font-medium text-red-500 hover:text-red-400 transition-colors break-words">
                                            {{ $thread->title }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-400 mt-2 flex flex-wrap items-center gap-2">
                                        <span>by <span class="text-gray-300">{{ $thread->user->name }}</span></span>
                                        <span class="text-gray-600">•</span>
                                        <span>{{ $thread->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <div class="flex lg:hidden gap-6 text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400">Replies:</span>
                                        <span class="text-gray-300 font-medium">{{ $thread->posts_count ?? 0 }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400">Likes:</span>
                                        <span class="text-gray-300 font-medium">{{ $thread->likes_count ?? 0 }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400">Views:</span>
                                        <span class="text-gray-300 font-medium">{{ number_format($thread->views) }}</span>
                                    </div>
                                </div>

                                <div class="hidden lg:flex lg:w-24 justify-center">
                                    <span class="text-gray-300 font-medium">{{ $thread->posts_count ?? 0 }}</span>
                                </div>
                                <div class="hidden lg:flex lg:w-24 justify-center">
                                    <span class="text-gray-300 font-medium">{{ $thread->likes_count ?? 0 }}</span>
                                </div>
                                <div class="hidden lg:flex lg:w-24 justify-center">
                                    <span class="text-gray-300 font-medium">{{ number_format($thread->views) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <p class="text-gray-400 text-lg">No threads in this category yet.</p>
                            @auth
                                <a href="{{ route('forum.thread.create', $category->slug) }}" class="inline-block mt-4 text-red-500 hover:text-red-400 font-medium transition-colors">
                                    Be the first to create one!
                                </a>
                            @endauth
                        </div>
                    @endforelse
                </div>
            </div>

            @if($threads->hasPages())
                <div class="mt-8">
                    {{ $threads->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
