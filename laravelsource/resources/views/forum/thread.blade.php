@extends('layouts.app')

@section('title', $thread->title)

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
                    <a href="{{ route('forum.category', $thread->category->slug) }}" class="hover:text-white transition-colors">{{ $thread->category->name }}</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-500">{{ Str::limit($thread->title, 30) }}</span>
                </nav>

                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl md:text-4xl font-bold break-words">{{ $thread->title }}</h1>
                        <div class="flex flex-wrap items-center gap-3 mt-4 text-sm text-gray-400">
                            <span>by <span class="text-gray-300 font-medium">{{ $thread->user->name }}</span></span>
                            <span class="text-gray-600">•</span>
                            <span>{{ $thread->created_at->format('M d, Y') }}</span>
                            <span class="text-gray-600">•</span>
                            <span>{{ number_format($thread->views) }} views</span>
                        </div>
                    </div>
                    @auth
                        <form action="{{ route('forum.thread.like', $thread) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 px-6 py-3 border border-gray-700 hover:bg-gray-800/50 rounded-lg transition-all">
                                <svg class="w-5 h-5 {{ $thread->likes->where('user_id', auth()->id())->count() > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                </svg>
                                <span class="text-gray-300 font-medium">{{ $thread->likes->count() }}</span>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>

<div class="space-y-6">
    @foreach($posts as $post)
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl overflow-hidden backdrop-blur-sm">
            <div class="flex flex-col lg:flex-row">
                <div class="w-full lg:w-56 bg-gray-800/50 p-6 border-b lg:border-b-0 lg:border-r border-gray-700">
                    <div class="flex lg:flex-col items-center lg:text-center gap-4 lg:gap-0">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 bg-gradient-to-br from-red-600 to-red-800 rounded-full lg:mx-auto flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl lg:text-3xl text-white font-bold">{{ substr($post->user->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 lg:flex-none lg:mt-4">
                            <div class="font-semibold text-white text-lg">{{ $post->user->name }}</div>
                            <div class="text-xs text-gray-400 mt-1.5">
                                Joined {{ $post->user->created_at->format('M Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 p-6 lg:p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div class="text-sm text-gray-400">
                            Posted {{ $post->created_at->diffForHumans() }}
                        </div>
                        @auth
                            @if($post->user_id === auth()->id() || auth()->user()->is_admin)
                                <form action="{{ route('forum.post.destroy', $post) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 text-sm font-medium transition-colors">Delete</button>
                                </form>
                            @endif
                        @endauth
                    </div>

                    <div class="text-gray-200 leading-relaxed break-words whitespace-pre-wrap">
                        {!! nl2br(e($post->content)) !!}
                    </div>

                    <div class="flex items-center mt-6 pt-6 border-t border-gray-800/50">
                        @auth
                            <form action="{{ route('forum.post.like', $post) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-800/50 transition-all">
                                    <svg class="w-5 h-5 {{ $post->likes->where('user_id', auth()->id())->count() > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                    </svg>
                                    <span class="text-gray-300 font-medium">{{ $post->likes->count() }}</span>
                                </button>
                            </form>
                        @else
                            <div class="flex items-center gap-2 text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                </svg>
                                <span class="font-medium">{{ $post->likes->count() }}</span>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($posts->hasPages())
    <div class="mt-8">
        {{ $posts->links() }}
    </div>
@endif

<!-- Reply Section -->
<div class="mt-8">
    @auth
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6 backdrop-blur-sm">
            <h3 class="text-xl font-semibold mb-4">Reply to this thread</h3>
            <form action="{{ route('forum.post.store', $thread->slug) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="reply-content" class="block text-sm font-medium mb-2 text-gray-300">Your Reply</label>
                    <textarea id="reply-content" name="content" rows="6" required
                        class="w-full px-4 py-3 rounded-lg bg-black border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 resize-vertical"
                        placeholder="Write your reply...">{{ old('content') }}</textarea>
                    @error('content')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white font-medium transition-colors">
                        Post Reply
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-gradient-to-r from-gray-900/80 to-gray-800/80 border border-gray-700 rounded-xl p-8 backdrop-blur-sm text-center">
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-white mb-2">Login Required</h3>
                    <p class="text-gray-400 mb-6">You must be logged in to reply to this thread and interact with the community.</p>
                    <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 rounded-lg text-white font-medium transition-colors">
                        Sign in to Reply
                    </a>
                </div>
            </div>
        @endauth
        </div>
    </div>
</div>
@endsection
