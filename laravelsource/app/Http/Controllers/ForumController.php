<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class ForumController extends Controller
{
    public function index(): View
    {
        // Cache the categories list for 10 minutes
        $categories = Cache::remember('forum.categories.index', 600, function () {
            return ForumCategory::with(['children', 'threads' => function ($query) {
                $query->latest()->limit(5);
            }])
                ->withCount('threads')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        });

        return view('forum.index', compact('categories'));
    }

    public function category(string $slug): View
    {
        $category = ForumCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $threads = ForumThread::with(['user', 'posts', 'likes'])
            ->withCount(['posts', 'likes'])
            ->where('category_id', $category->id)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('forum.category', compact('category', 'threads'));
    }

    public function search(Request $request): View
    {
        $query = $request->input('q');
        
        $threads = ForumThread::with(['user', 'category'])
            ->withCount('posts')
            ->where('title', 'like', "%{$query}%")
            ->orWhereHas('posts', function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('forum.search', compact('threads', 'query'));
    }
}
