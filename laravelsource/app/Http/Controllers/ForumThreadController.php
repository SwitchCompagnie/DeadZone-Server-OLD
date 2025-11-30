<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\ForumLike;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ForumThreadController extends Controller
{
    public function show(string $slug): View
    {
        $thread = ForumThread::with(['user', 'category', 'posts.user', 'posts.likes', 'likes'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count
        $thread->incrementViews();

        $posts = $thread->posts()
            ->with(['user' => function ($query) {
                $query->withCount('forumPosts');
            }, 'likes'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('forum.thread', compact('thread', 'posts'));
    }

    public function create(string $categorySlug): View
    {
        $category = ForumCategory::where('slug', $categorySlug)->firstOrFail();
        return view('forum.create-thread', compact('category'));
    }

    public function store(Request $request, string $categorySlug): RedirectResponse
    {
        $category = ForumCategory::where('slug', $categorySlug)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        $thread = ForumThread::create([
            'title' => $validated['title'],
            'category_id' => $category->id,
            'user_id' => Auth::id(),
        ]);

        // Create first post
        $thread->posts()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('forum.thread.show', $thread->slug)
            ->with('success', 'Thread created successfully!');
    }

    public function like(ForumThread $thread): RedirectResponse
    {
        $user = Auth::user();
        
        $existingLike = ForumLike::where('user_id', $user->id)
            ->where('likeable_id', $thread->id)
            ->where('likeable_type', ForumThread::class)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'likeable_id' => $thread->id,
                'likeable_type' => ForumThread::class,
            ]);
        }

        return back();
    }
}
