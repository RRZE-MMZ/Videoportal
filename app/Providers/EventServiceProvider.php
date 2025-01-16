<?php

namespace App\Providers;

use App\Events\AssetDeleted;
use App\Events\ChapterDeleted;
use App\Events\ClipDeleting;
use App\Events\DocumentDeleted;
use App\Events\SeriesDeleted;
use App\Events\SeriesTitleUpdated;
use App\Events\UserExpired;
use App\Listeners\DeleteAssetFile;
use App\Listeners\DeleteClipAssets;
use App\Listeners\DeleteDocumentFile;
use App\Listeners\DeleteSeriesAssets;
use App\Listeners\HandleUserExpiration;
use App\Listeners\Saml2UserSignedIn;
use App\Listeners\Saml2UserSignedOut;
use App\Listeners\UpdateClipChapter;
use App\Listeners\UpdateOpencastSeriesTitle;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Slides\Saml2\Events\SignedIn as Saml2SignedIn;
use Slides\Saml2\Events\SignedOut as Saml2SignedOut;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Saml2SignedIn::class => [
            Saml2UserSignedIn::class,
        ],
        Saml2SignedOut::class => [
            Saml2UserSignedOut::class,
        ],
        AssetDeleted::class => [
            DeleteAssetFile::class,
        ],
        DocumentDeleted::class => [
            DeleteDocumentFile::class,
        ],
        ChapterDeleted::class => [
            UpdateClipChapter::class,
        ],
        ClipDeleting::class => [
            DeleteClipAssets::class,
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SeriesDeleted::class => [
            DeleteSeriesAssets::class,
        ],
        SeriesTitleUpdated::class => [
            UpdateOpencastSeriesTitle::class,
        ],
        UserExpired::class => [
            HandleUserExpiration::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void {}
}
