@extends('layouts.app')

@section('title', 'Create New Thread')

@section('content')
<div class="min-h-screen bg-black">
    @include('partials.forum-nav')

    <!-- Content -->
    <div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <div class="text-sm text-gray-400 mb-2">
                    <a href="{{ route('forum.index') }}" class="hover:text-white">Forum</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('forum.category', $category->slug) }}" class="hover:text-white">{{ $category->name }}</a>
                    <span class="mx-2">/</span>
                    <span>New Thread</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold">Create New Thread in {{ $category->name }}</h1>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 sm:p-6">
    <form action="{{ route('forum.thread.store', $category->slug) }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium mb-2 text-gray-300">Thread Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                class="w-full px-4 py-3 rounded-lg bg-black border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:border-red-500"
                placeholder="Enter your thread title...">
            @error('title')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="content" class="block text-sm font-medium mb-2 text-gray-300">Content</label>
            <textarea id="content" name="content" rows="10" required
                class="w-full px-4 py-3 rounded-lg bg-black border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 resize-vertical"
                placeholder="Write your post content...">{{ old('content') }}</textarea>
            @error('content')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
            <a href="{{ route('forum.category', $category->slug) }}" 
               class="w-full sm:w-auto px-6 py-2 border border-gray-700 hover:bg-gray-800 rounded-lg text-gray-300 font-medium transition-colors text-center">
                Cancel
            </a>
            <button type="submit" 
                class="w-full sm:w-auto px-6 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white font-medium transition-colors">
                    Create Thread
                </button>
            </div>
        </form>
            </div>
        </div>
    </div>
</div>
@endsection
