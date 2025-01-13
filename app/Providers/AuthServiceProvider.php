<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Channel;
use App\Models\Clip;
use App\Models\Comment;
use App\Models\Podcast;
use App\Models\Series;
use App\Models\User;
use App\Policies\AssetPolicy;
use App\Policies\ChannelsPolicy;
use App\Policies\ClipPolicy;
use App\Policies\CommentPolicy;
use App\Policies\PodcastPolicy;
use App\Policies\SeriesPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Clip::class => ClipPolicy::class,
        Asset::class => AssetPolicy::class,
        Series::class => SeriesPolicy::class,
        Comment::class => CommentPolicy::class,
        User::class => UserPolicy::class,
        Channel::class => ChannelsPolicy::class,
        Podcast::class => PodcastPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // series
        Gate::define('index-all-series', [SeriesPolicy::class, 'index']);
        Gate::define('create-series', [SeriesPolicy::class, 'create']);
        Gate::define('edit-series', [SeriesPolicy::class, 'edit']);
        Gate::define('update-series', [SeriesPolicy::class, 'update']);
        Gate::define('view-series', [SeriesPolicy::class, 'view']);
        Gate::define('view-series-comments', [SeriesPolicy::class, 'viewComments']);
        Gate::define('delete-series', [SeriesPolicy::class, 'delete']);
        Gate::define('change-series-owner', [SeriesPolicy::class, 'changeOwner']);

        // clips
        Gate::define('index-all-clips', [ClipPolicy::class, 'index']);
        Gate::define('create-clips', [ClipPolicy::class, 'create']);
        Gate::define('edit-clips', [ClipPolicy::class, 'edit']);
        Gate::define('view-clips', [ClipPolicy::class, 'view']);
        Gate::define('view-clips-comments', [ClipPolicy::class, 'viewComments']);
        Gate::define('view-video', [ClipPolicy::class, 'viewVideo']);
        Gate::define('watch-video', [ClipPolicy::class, 'canWatchVideo']);
        Gate::define('edit-assets', [AssetPolicy::class, 'edit']);
        Gate::define('download-asset', [AssetPolicy::class, 'download']);
        // user
        Gate::define('show-users', [UserPolicy::class, 'show']);
        Gate::define('access-dashboard', [UserPolicy::class, 'dashboard']);
        Gate::define('administrate-moderator-pages',
            fn (User $user) => $user->isModerator() || $user->isAssistant() || $user->isAdmin());
        Gate::define('administrate-assistant-pages', fn (User $user) => $user->isAssistant() || $user->isAdmin());
        Gate::define('administrate-admin-portal-pages', fn (User $user) => $user->isAdmin());
        Gate::define('administrate-superadmin-portal-pages', fn (User $user) => $user->isSuperAdmin());

        // comments
        Gate::define('create-comment', [CommentPolicy::class, 'create']);
        Gate::define('delete-comment', [CommentPolicy::class, 'delete']);

        // channels
        Gate::define('activate-channel', [ChannelsPolicy::class, 'create']);
        Gate::define('edit-channel', [ChannelsPolicy::class, 'update']);

        // podcasts
        Gate::define('edit-podcast', [PodcastPolicy::class, 'update']);
        Gate::define('create-podcasts', [PodcastPolicy::class, 'create']);
    }
}
