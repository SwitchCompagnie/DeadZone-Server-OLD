@extends('layouts.app')

@section('title', 'Forum Home')

@section('content')
<div class="min-h-screen bg-black">
    @include('partials.forum-nav')

    <!-- Content -->
    <div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10">
                <h1 class="text-3xl md:text-4xl font-bold">Forum</h1>
                <p class="text-gray-400 mt-3 text-lg">Welcome to the Deadzone Revive community forum</p>
            </div>

            <div class="space-y-6">
                @forelse($categories as $category)
                    <div class="bg-gray-900/50 border border-gray-800 rounded-xl overflow-hidden backdrop-blur-sm">
                        <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700">
                            <h2 class="text-xl md:text-2xl font-semibold">{{ $category->name }}</h2>
                            @if($category->description)
                                <p class="text-gray-400 mt-2">{{ $category->description }}</p>
                            @endif
                        </div>

                        <div class="divide-y divide-gray-800/50">
                            @if($category->children->count() > 0)
                                @foreach($category->children as $subcategory)
                                    <a href="{{ route('forum.category', $subcategory->slug) }}" class="block px-6 py-5 hover:bg-gray-800/50 transition-all">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-lg font-medium text-red-500 hover:text-red-400 transition-colors">
                                                    {{ $subcategory->name }}
                                                </h3>
                                                @if($subcategory->description)
                                                    <p class="text-gray-400 mt-1.5 text-sm line-clamp-2">{{ $subcategory->description }}</p>
                                                @endif
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-800 text-gray-300">
                                                    {{ $subcategory->threads_count ?? 0 }} threads
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @else
                                <a href="{{ route('forum.category', $category->slug) }}" class="block px-6 py-5 hover:bg-gray-800/50 transition-all">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-red-500 hover:text-red-400 transition-colors">
                                                View all threads
                                            </h3>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-800 text-gray-300">
                                                {{ $category->threads_count ?? 0 }} threads
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-12 text-center">
                        <p class="text-gray-400 text-lg">No forum categories available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
