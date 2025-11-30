<?php

namespace App\Observers;

use App\Models\ForumThread;
use Illuminate\Support\Facades\Cache;

class ForumThreadObserver
{
    /**
     * Handle the ForumThread "created" event.
     */
    public function created(ForumThread $forumThread): void
    {
        Cache::forget('forum.categories.index');
    }

    /**
     * Handle the ForumThread "updated" event.
     */
    public function updated(ForumThread $forumThread): void
    {
        Cache::forget('forum.categories.index');
    }

    /**
     * Handle the ForumThread "deleted" event.
     */
    public function deleted(ForumThread $forumThread): void
    {
        Cache::forget('forum.categories.index');
    }

    /**
     * Handle the ForumThread "restored" event.
     */
    public function restored(ForumThread $forumThread): void
    {
        Cache::forget('forum.categories.index');
    }

    /**
     * Handle the ForumThread "force deleted" event.
     */
    public function forceDeleted(ForumThread $forumThread): void
    {
        Cache::forget('forum.categories.index');
    }
}
