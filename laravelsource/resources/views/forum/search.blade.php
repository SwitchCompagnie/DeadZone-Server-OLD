@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="min-h-screen bg-black">
    @include('partials.forum-nav')

    <!-- Content -->
    <div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold">Search Results</h1>
                <p class="text-gray-400 mt-2">Searching for: "{{ $query }}"</p>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
                <div class="divide-y divide-gray-800">
                    @forelse($threads as $thread)
                        <div class="px-4 sm:px-6 py-4 hover:bg-gray-800 transition-colors">
                            <div class="flex flex-col sm:flex-row items-start justify-between gap-2 sm:gap-0">
                                <div class="flex-1">
                                    <a href="{{ route('forum.thread.show', $thread->slug) }}" 
                                       class="text-base sm:text-lg font-medium text-red-500 hover:text-red-400 break-words">
                                        {{ $thread->title }}
                                    </a>
                                    <div class="text-sm text-gray-400 mt-1">
                                        in <a href="{{ route('forum.category', $thread->category->slug) }}" class="text-gray-300 hover:text-white">{{ $thread->category->name }}</a>
                                        • by <span class="text-gray-300">{{ $thread->user->name }}</span>
                                        • {{ $thread->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-400 sm:ml-4">
                                    {{ $thread->posts_count ?? 0 }} replies
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 sm:px-6 py-8 text-center text-gray-400">
                            No threads found matching your search.
                        </div>
                    @endforelse
                </div>
            </div>

            @if($threads->hasPages())
                <div class="mt-6">
                    {{ $threads->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
