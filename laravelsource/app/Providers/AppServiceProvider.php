<?php

namespace App\Providers;

use App\Models\ForumThread;
use App\Observers\ForumThreadObserver;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Discord\DiscordExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(
            SocialiteWasCalled::class,
            DiscordExtendSocialite::class
        );

        // Register forum observers
        ForumThread::observe(ForumThreadObserver::class);
    }
}
