<?php

namespace App\Http\Controllers;

use App\Models\ForumThread;
use App\Models\ForumPost;
use App\Models\ForumLike;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ForumPostController extends Controller
{
    public function store(Request $request, ForumThread $thread): RedirectResponse
    {
        if ($thread->is_locked) {
            return back()->with('error', 'This thread is locked.');
        }

        $validated = $request->validate([
            'content' => 'required|string|min:5',
            'parent_id' => 'nullable|exists:forum_posts,id',
        ]);

        $thread->posts()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return back()->with('success', 'Reply posted successfully!');
    }

    public function like(ForumPost $post): RedirectResponse
    {
        $user = Auth::user();
        
        $existingLike = ForumLike::where('user_id', $user->id)
            ->where('likeable_id', $post->id)
            ->where('likeable_type', ForumPost::class)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'likeable_id' => $post->id,
                'likeable_type' => ForumPost::class,
            ]);
        }

        return back();
    }

    public function destroy(ForumPost $post): RedirectResponse
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $post->delete();

        return back()->with('success', 'Post deleted successfully!');
    }
}
