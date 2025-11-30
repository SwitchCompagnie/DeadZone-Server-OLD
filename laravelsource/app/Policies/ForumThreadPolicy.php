<?php

namespace App\Policies;

use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ForumThreadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view threads
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, ForumThread $forumThread): bool
    {
        return true; // Anyone can view a thread
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->email_verified_at !== null; // Must have verified email
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ForumThread $forumThread): bool
    {
        return $user->id === $forumThread->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ForumThread $forumThread): bool
    {
        return $user->id === $forumThread->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ForumThread $forumThread): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ForumThread $forumThread): bool
    {
        return $user->is_admin;
    }
}
