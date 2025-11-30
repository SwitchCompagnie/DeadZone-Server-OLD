<?php

namespace Database\Seeders;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories
        $general = ForumCategory::create([
            'name' => 'General Discussion',
            'description' => 'General topics and discussions',
            'slug' => 'general-discussion',
            'order' => 1,
            'is_active' => true,
        ]);

        $gameDiscussion = ForumCategory::create([
            'name' => 'Game Discussion',
            'description' => 'Discuss all things about Deadzone',
            'slug' => 'game-discussion',
            'order' => 2,
            'is_active' => true,
        ]);

        $support = ForumCategory::create([
            'name' => 'Support',
            'description' => 'Get help with technical issues',
            'slug' => 'support',
            'order' => 3,
            'is_active' => true,
        ]);

        $announcements = ForumCategory::create([
            'name' => 'Announcements',
            'description' => 'Official announcements and updates',
            'slug' => 'announcements',
            'order' => 0,
            'is_active' => true,
        ]);

        // Create subcategories
        ForumCategory::create([
            'name' => 'Bug Reports',
            'description' => 'Report bugs and issues',
            'slug' => 'bug-reports',
            'parent_id' => $support->id,
            'order' => 1,
            'is_active' => true,
        ]);

        ForumCategory::create([
            'name' => 'Feature Requests',
            'description' => 'Suggest new features',
            'slug' => 'feature-requests',
            'parent_id' => $general->id,
            'order' => 1,
            'is_active' => true,
        ]);

        // Get the first user or create a demo user
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Create sample threads
        $welcomeThread = ForumThread::create([
            'title' => 'Welcome to Deadzone Revive Forum!',
            'slug' => 'welcome-to-deadzone-revive-forum',
            'category_id' => $announcements->id,
            'user_id' => $user->id,
            'is_pinned' => true,
        ]);

        ForumPost::create([
            'content' => 'Welcome to the official Deadzone Revive forum! This is a place where you can discuss the game, get help, and connect with other players. Please be respectful and follow the community guidelines.',
            'thread_id' => $welcomeThread->id,
            'user_id' => $user->id,
        ]);

        $gameThread = ForumThread::create([
            'title' => 'What are your favorite game features?',
            'slug' => 'what-are-your-favorite-game-features',
            'category_id' => $gameDiscussion->id,
            'user_id' => $user->id,
        ]);

        ForumPost::create([
            'content' => 'I\'d love to hear what features you enjoy the most in Deadzone! Share your thoughts and let\'s discuss what makes this game great.',
            'thread_id' => $gameThread->id,
            'user_id' => $user->id,
        ]);
    }
}

