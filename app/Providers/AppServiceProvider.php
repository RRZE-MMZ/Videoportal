<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

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
        if (! app()->environment('production')) {
            Mail::alwaysTo(env('DEV_MAIL_ADDRESS'));
        }
        Relation::enforceMorphMap([
            'series' => 'App\Models\Series',
            'clip' => 'App\Models\Clip',
            'user' => 'App\Models\User',
            'image' => 'App\Models\Image',
            'semester' => 'App\Models\Semester',
            'podcasts' => 'App\Models\Podcast',
            'podcast_episode' => 'App\Models\PodcastEpisode',
        ]);
    }
}
