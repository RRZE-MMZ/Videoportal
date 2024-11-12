<?php

use App\Http\Controllers\Backend\AdminPortalActivateChannelController;
use App\Http\Controllers\Backend\AdminPortalApplicationController;
use App\Http\Controllers\Backend\ArticlesController;
use App\Http\Controllers\Backend\AssetDestroyController;
use App\Http\Controllers\Backend\AssetsTransferController;
use App\Http\Controllers\Backend\ChannelsController;
use App\Http\Controllers\Backend\ChannelsUploadBannerImageController;
use App\Http\Controllers\Backend\ChaptersController;
use App\Http\Controllers\Backend\ClipsCollectionsController;
use App\Http\Controllers\Backend\ClipsController;
use App\Http\Controllers\Backend\ClipsPlayerActionsController;
use App\Http\Controllers\Backend\CollectionsController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DevicesController;
use App\Http\Controllers\Backend\DocumentController;
use App\Http\Controllers\Backend\FileUploadController;
use App\Http\Controllers\Backend\ImagesController;
use App\Http\Controllers\Backend\LivestreamsController;
use App\Http\Controllers\Backend\ManageLivestreamRoom;
use App\Http\Controllers\Backend\PodcastEpisodesController;
use App\Http\Controllers\Backend\PodcastsController;
use App\Http\Controllers\Backend\PortalSettingsController;
use App\Http\Controllers\Backend\PresentersController;
use App\Http\Controllers\Backend\SearchSettingsController;
use App\Http\Controllers\Backend\SeriesClipsController;
use App\Http\Controllers\Backend\SeriesController;
use App\Http\Controllers\Backend\SeriesMembershipController;
use App\Http\Controllers\Backend\SeriesOwnership;
use App\Http\Controllers\Backend\SeriesVideoWorkflowController;
use App\Http\Controllers\Backend\StatisticsController;
use App\Http\Controllers\Backend\StreamingSettingsController;
use App\Http\Controllers\Backend\SystemsCheckController;
use App\Http\Controllers\Backend\TriggerSmilFilesController;
use App\Http\Controllers\Backend\UpdateClipImage;
use App\Http\Controllers\Backend\UpdateSeriesImage;
use App\Http\Controllers\Backend\UploadImageController;
use App\Http\Controllers\Backend\UserNotificationsController;
use App\Http\Controllers\Backend\UsersController;
use App\Http\Controllers\Backend\VideoWorkflowSettingsController;
use App\Http\Controllers\Frontend\AcceptUseTermsController;
use App\Http\Controllers\Frontend\AdminPortalUseTermsController;
use App\Http\Controllers\Frontend\ApiController;
use App\Http\Controllers\Frontend\AssetsDownloadController;
use App\Http\Controllers\Frontend\FeedsController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShowChannelsController;
use App\Http\Controllers\Frontend\ShowClipsController;
use App\Http\Controllers\Frontend\ShowLivestreamsController;
use App\Http\Controllers\Frontend\ShowOrganizationsController;
use App\Http\Controllers\Frontend\ShowPodcastsController;
use App\Http\Controllers\Frontend\ShowSearchResultsController;
use App\Http\Controllers\Frontend\ShowSeriesController;
use App\Http\Controllers\Frontend\UserApplicationsController;
use App\Http\Controllers\Frontend\UserCommentsController;
use App\Http\Controllers\Frontend\UserSettingsController;
use App\Http\Controllers\Frontend\UserSubscriptionsController;
use App\Models\Activity;
use App\Models\Article;
use App\Models\Clip;
use App\Models\Series;
use App\Providers\RouteServiceProvider;
use App\Services\OpenSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::redirect('/home', '/');
Route::redirect('/admin', '/admin/dashboard');

//Quick __invoke
Route::get('/search', ShowSearchResultsController::class)->name('search');

//Channels routes
Route::get('/channels', [ShowChannelsController::class, 'index'])->name('frontend.channels.index');
Route::get('/channels/{channel}', [ShowChannelsController::class, 'show'])->name('frontend.channels.show');

//frontend series routes
Route::controller(ShowSeriesController::class)->prefix('/series')->group(function () {
    Route::get('/index', 'index')->name('frontend.series.index');
    Route::get('/{series}', 'show')->name('frontend.series.show');
});

Route::get('/series/{series}/feed/{assetsResolution}', [FeedsController::class, 'series'])
    ->name('frontend.series.feed');

//keep backwards compatibility
Route::get('/course/id/{series}', function (Series $series) {
    return to_route('frontend.series.show', $series);
});

//Frontend clip routes
Route::controller(ShowClipsController::class)->prefix('/clips')->group(function () {
    Route::get('/', 'index')->name('frontend.clips.index');
    Route::get('/{clip}', 'show')->name('frontend.clips.show');
});

//Route::get('/webplayer', TestApiController::class)->name('frontend.webplayer');C
Route::get('/clips/{clip}/feed/{assetsResolution}', [FeedsController::class, 'clips'])
    ->name('frontend.clips.feed');

//Frontend podcasts routes
Route::controller(ShowPodcastsController::class)->prefix('/podcasts')->group(function () {
    Route::get('/', [ShowPodcastsController::class, 'index'])->name('frontend.podcasts.index');
    Route::get('/{podcast:slug}', [ShowPodcastsController::class, 'show'])->name('frontend.podcasts.show');
    Route::get('/{podcast:slug}/episodes/{episode:slug}', [ShowPodcastsController::class, 'episode'])
        ->name('frontend.podcasts.episode.show');
});

Route::get('/organizations/', [ShowOrganizationsController::class, 'index'])
    ->name('frontend.organizations.index');
Route::get('/organizations/{organization:slug}', [ShowOrganizationsController::class, 'show'])
    ->name('frontend.organizations.show');

Route::get('/live-now', [ShowLivestreamsController::class, 'index'])->name('frontend.livestreams.index');
Route::get('/livestreams/{livestream:id}', [ShowLivestreamsController::class, 'show'])
    ->name('frontend.livestreams.show');

//static pages
Route::get('/faq', function () {
    return view('frontend.articles.show')->withArticle(Article::whereSlug('faq')->first());
})->name('frontend.faq');
Route::get('/contact', function () {
    return view('frontend.articles.show')->withArticle(Article::whereSlug('contact')->first());
})->name('frontend.contact');
Route::get('/imprint', function () {
    return view('frontend.articles.show')->withArticle(Article::whereSlug('imprint')->first());
})->name('frontend.imprint');
Route::get('/privacy', function () {
    return view('frontend.articles.show')->withArticle(Article::whereSlug('privacy')->first());
})->name('frontend.privacy');
Route::get('/accessibility', function () {
    return view('frontend.articles.show')->withArticle(Article::whereSlug('accessibility')->first());
})->name('frontend.accessibility');

//Frontend myPortal links
Route::prefix('/my'.str(config('app.name')))->middleware(['auth'])->group(function () {
    Route::put('/', AcceptUseTermsController::class)->name('frontend.acceptUseTerms');
    Route::middleware(['use.terms'])->group(function () {
        Route::get('/settings', [UserSettingsController::class, 'edit'])
            ->name('frontend.userSettings.edit');
        Route::put('/settings', [UserSettingsController::class, 'update'])
            ->name('frontend.userSettings.update');
        Route::get('/subscriptions', UserSubscriptionsController::class)
            ->name('frontend.user.subscriptions');
        Route::get('/comments', UserCommentsController::class)
            ->name('frontend.user.comments');
        Route::get('/adminPortal/useTerms', [AdminPortalUseTermsController::class, 'terms'])
            ->name('frontend.admin.portal.use.terms');
        Route::put('/adminPortal/useTerms', [AdminPortalUseTermsController::class, 'accept'])
            ->name('frontend.admin.portal.accept.use.terms');
        Route::get('/applications', UserApplicationsController::class)
            ->name('frontend.user.applications');
    });
});

Route::get(
    '/protector/link/{objType}/{objID}/{token}/{time}/{client}',
    function (string $objType, int $objID, $token, $time, $client) {
        $objType = getUrlTokenType($objType);
        $client = getUrlClientType($client);

        $model = "App\Models\\".ucfirst($objType);

        $obj = $model::findorFail($objID);

        setSessionAccessToken($obj, $token, $time, $client);

        return to_route('frontend.'.str($objType)->plural().'.show', $obj);
    }
)
    ->middleware(['access.token'])
    ->name('lms.link');

//Routes used for select2 js component
Route::controller(ApiController::class)->prefix('/api')->group(function () {
    Route::get('/clips', 'clips')->name('api.clips');
    Route::get('/tags', 'tags')->name('api.tags');
    Route::get('/presenters', 'presenters')->name('api.presenters');
    Route::get('/users', 'users')->name('api.users');
    Route::get('/organizations', 'organizations')->name('api.organizations');
    Route::get('/images', 'images')->name('api.images');
    Route::get('/roles', 'roles')->name('api.roles');
    Route::post('/logPlayEvent', [ApiController::class, 'logPlayEvent'])->name('api.logPlayEvent');
});

//change portal language
Route::get('/set_lang/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'de'])) {
        abort(400);
    }
    session()->put('locale', $locale);

    return back();
});

Route::get('/assetDownload/{asset:guid}', AssetsDownloadController::class)->name('assets.download');

//Backend routes
Route::prefix('admin')->middleware(['auth', 'saml', 'can:access-dashboard'])->group(function () {
    //Dashboard
    Route::get('search', \App\Http\Controllers\Backend\ShowSearchResultsController::class)->name('admin.search');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/goto/series', function (Request $request) {
        $validated = $request->validate([
            'seriesID' => ['required', 'integer'],
        ]);
        $series = Series::find($validated['seriesID']);

        return (is_null($series)) ? to_route('dashboard')->with('flashMessage', 'Series not found')
            : to_route('series.edit', $series);
    })->name('goto.series');

    Route::post('/goto/clip', function (Request $request) {
        $validated = $request->validate([
            'clipID' => ['required', 'integer'],
        ]);
        $clip = Clip::find($validated['clipID']);

        return (is_null($clip)) ? to_route('dashboard')->with('flashMessage', 'Clip not found')
            : to_route('clips.edit', $clip);
    })->name('goto.clip');

    Route::get('/notifications', [UserNotificationsController::class, 'index'])
        ->name('user.notifications');
    Route::delete('/notifications', [UserNotificationsController::class, 'destroy'])
        ->name('user.notifications.delete');

    //Channels routes
    Route::resource('channels', ChannelsController::class)->except(['show']);
    Route::post('channel/{channel}/uploadChannelBannerImage', ChannelsUploadBannerImageController::class)
        ->name('channels.uploadBannerImage');
    //Series routes
    Route::resource('series', SeriesController::class)->except(['show', 'edit']);
    Route::get('/series/{series}', [SeriesController::class, 'edit'])->name('series.edit');

    Route::controller(SeriesClipsController::class)->prefix('/series')->group(function () {
        // Create a clip for a certain series.
        Route::get('/{series}/addClip', 'create')->name('series.clips.create');
        Route::post('/{series}/addClip', 'store')->name('series.clips.store');

        Route::get('/{series}/reorder', 'listClips')->name('series.clips.changeEpisode');
        Route::post('/{series}/reorder', 'reorder')->name('series.clips.reorder');

        Route::get('/{series}/clipsMetadata', 'showClipsMetadata')->name('series.clips.batch.show.clips.metadata');
        Route::patch('/{series}/updateClipsMetadata', 'updateClipsMetadata')
            ->name('series.clips.batch.update.clips.metadata');

        //add/remove an existing clip to selected series
        Route::get('/listSeries/{clip}', 'listSeries')->name('series.clips.listSeries');
        Route::post('/{series}/assignSeries/{clip}', 'assign')->name('series.clips.assign');
        Route::delete('/clip/removeSeries/{clip}', 'remove')->name('series.clips.remove');
    });

    //Series chapters
    Route::controller(ChaptersController::class)->prefix('/series')
        ->middleware('can:edit,series')
        ->group(function () {
            Route::get('/{series}/chapters', 'index')->name('series.chapters.index');
            Route::post('/{series}/chapters', 'store')->name('series.chapters.create');
            Route::put('/{series}/chapters/', 'update')->name('series.chapters.update');
            Route::get('/{series}/chapters/{chapter}', 'edit')->name('series.chapters.edit');
            Route::patch('/{series}/chapters/{chapter}/addClips', 'addClips')->name('series.chapters.addClips');
            Route::patch('/{series}/chapters/{chapter}/removeClips', 'removeClips')
                ->name('series.chapters.removeClips');
            Route::delete('/{series}/chapters/{chapter}', 'destroy')->name('series.chapters.delete');
            // Series Statistic
            Route::get('{series}/statistics', [StatisticsController::class, 'series'])->name('statistics.series');
        });

    //Series invitations - Invite a user to be a member of a Series
    Route::post('/series/{series}/ownership', SeriesOwnership::class)->name('series.ownership.change');
    Route::post('/series/{series}/membership/addUser', [SeriesMembershipController::class, 'add'])
        ->name('series.membership.addUser');
    Route::post('/series/{series}/membership/removeUser', [SeriesMembershipController::class, 'remove'])
        ->name('series.membership.removeUser');
    // Series Images
    Route::put('/series/{series}/updateImage/', UpdateSeriesImage::class)->name('update.series.image');

    //Clip routes
    Route::resource('clips', ClipsController::class)->except(['show']);

    //Podcast routes
    Route::resource('podcasts', PodcastsController::class)->except(['show']);
    Route::controller(PodcastEpisodesController::class)->prefix('/podcasts')
        ->middleware('can:edit-podcast,podcast')
        ->group(function () {
            Route::get('/{podcast}/episodes', 'create')->name('podcasts.episodes.create');
            Route::post('{podcast}/episodes', 'store')->name('podcasts.episodes.store');
            Route::get('/{podcast}/episodes/{episode:slug}', 'edit')->name('podcasts.episodes.edit');
            Route::put('/{podcast}/episodes/{episode:slug}', 'update')->name('podcasts.episodes.update');
            Route::delete('/{podcast}/episodes/{episode:slug}', 'destroy')->name('podcasts.episodes.destroy');
        });
    Route::post(
        '/podcasts/{podcast}/episodes/{episode:slug}/transferPodcastFile',
        [AssetsTransferController::class, 'transferPodcastAudioFile']
    )->name('admin.podcasts.episode.transferPodcastAudioFile');

    /*
     * A group of clip assets functions
     */
    Route::middleware('can:edit,clip')->group(function () {
        Route::controller(AssetsTransferController::class)->prefix('/clips')->group(function () {
            Route::post('/{clip}/generatePreviewImageFromFrame', [
                ClipsPlayerActionsController::class, 'generatePreviewImageFromFrame',
            ])->name('clips.generatePreviewImageFromFrame');
            Route::post('/{clip}/generatePreviewImageFromUser', [
                ClipsPlayerActionsController::class, 'generatePreviewImageFromUser',
            ])->name('clips.generatePreviewImageFromUser');
            Route::post('/{clip}/transferSingle', 'transferSingleAsset')->name('admin.clips.asset.transferSingle');

            Route::get('/{clip}/dropzone/list', 'listDropzoneFiles')->name('admin.clips.dropzone.listFiles');
            Route::post('/{clip}/dropzone/transfer', 'transferDropzoneFiles')->name('admin.clips.dropzone.transfer');
            /*
             * Transfer assets from Opencast Server
             */
            Route::get('/{clip}/opencast/list', 'listOpencastEvents')->name('admin.clips.opencast.listEvents');
            Route::post('/{clip}/opencast/transfer', 'transferOpencastFiles')->name('admin.clips.opencast.transfer');
        });

        // Clip Statistic
        Route::get('clip/{clip}/statistics', [StatisticsController::class, 'clip'])->name('statistics.clip');
    });

    //Clips Images
    Route::put('/clips/{clip}/updateImage/', UpdateClipImage::class)->name('update.clip.image');

    //Assets routes
    Route::delete('assets/{asset}', AssetDestroyController::class)->name('assets.destroy');

    //Documents routes
    Route::post('/document/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/series/{series}/document/{document}', [DocumentController::class, 'viewSeriesDocument'])
        ->name('document.series.view');
    Route::get('/clip/{clip}/document/{document}', [DocumentController::class, 'viewClipDocument'])
        ->name('document.clip.view');
    Route::delete('/document/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    //Presenter routes
    Route::resource('presenters', PresentersController::class)->except(['show']);

    Route::resource('images', ImagesController::class);
    Route::resource('devices', DevicesController::class)->except(['show']);
    Route::post('images/import/', UploadImageController::class)->name('images.import');
    Route::post('/uploads/process', [FileUploadController::class, 'process'])->name('uploads.process');
    Route::delete('/uploads/revert', [FileUploadController::class, 'revert'])->name('uploads.revert');

    // Portal admin resources (portal assistants are not included)
    Route::middleware(['user.admin'])->group(function () {
        Route::resource('users', UsersController::class)->except(['show']);

        //Collections administration
        Route::resource('collections', CollectionsController::class)->except(['show']);
        Route::post('collections/{collection}/toggleClips', ClipsCollectionsController::class)
            ->name('collections.toggleClips');

        //Articles
        Route::resource('articles', ArticlesController::class)->except(['show']);

        //Series Opencast routes
        Route::post('/series/{series}/createOpencastSeries/', [SeriesVideoWorkflowController::class, 'createSeries'])
            ->name('series.opencast.createSeries');
        Route::post('/series/{series}/updateOpencastSeriesAcl', [SeriesVideoWorkflowController::class, 'updateAcl'])
            ->name('series.opencast.updateSeriesAcl');
        Route::put('/series/{series}/updateOpencastSeriesTheme', [
            SeriesVideoWorkflowController::class, 'updateSeriesTheme'])
            ->name('series.opencast.updateSeriesTheme');
        Route::post('/series/{series}/updateScheduledEventsTitle', [
            SeriesVideoWorkflowController::class, 'updateEventsTitle',
        ])->name('series.opencast.updateEventsTitle');
        Route::post('/series/{series}/addScheduledEventsAsClips', [
            SeriesVideoWorkflowController::class, 'addScheduledEventsAsClips',
        ])->name('series.opencast.addScheduledEventsAsClips');

        Route::get('/clips/{clip}/triggerSmilFiles', TriggerSmilFilesController::class)
            ->name('admin.clips.triggerSmilFiles');
    });

    //Admin and portal assistants routes
    Route::middleware('can:administrate-assistant-pages')->group(function () {
        Route::get('/activities', function () {
            return view('backend.activities.index', [
                'activities' => Activity::paginate(20),
            ]);
        })->name('activities.index');
        Route::resource('livestreams', LivestreamsController::class)->except(['show']);
        Route::post('/livestreams/makeReservation', [ManageLivestreamRoom::class, 'reserve'])
            ->name('livestreams.makeReservation');
        Route::post('/livestreams/{livestream}/cancelReservation', [ManageLivestreamRoom::class, 'cancel'])
            ->name('livestreams.cancelReservation');
    });

    //Superadmin routes
    Route::middleware('can:administrate-superadmin-portal-pages')->group(function () {
        Route::get('/systems', SystemsCheckController::class)->name('systems.status');
        Route::get('/settings/portal', [PortalSettingsController::class, 'show'])->name('settings.portal.show');
        Route::put('/settings/portal', [PortalSettingsController::class, 'update'])->name('settings.portal.update');
        Route::get('/settings/workflow', [VideoWorkflowSettingsController::class, 'show'])
            ->name('settings.workflow.show');
        Route::put('/settings/workflow', [VideoWorkflowSettingsController::class, 'update'])
            ->name('settings.workflow.update');
        Route::get('/settings/workflow/fetchAndSaveOpencastThemes', [VideoWorkflowSettingsController::class, 'fetchAndSaveOpencastThemes'])
            ->name('settings.workflow.fetchAndSaveOpencastThemes');
        Route::get('/settings/streaming', [StreamingSettingsController::class, 'show'])
            ->name('settings.streaming.show');
        Route::put('/settings/streaming', [StreamingSettingsController::class, 'update'])
            ->name('settings.streaming.update');
        Route::get('/settings/openSearch', [SearchSettingsController::class, 'show'])
            ->name('settings.openSearch.show');
        Route::put('/settings/openSearch', [SearchSettingsController::class, 'update'])
            ->name('settings.openSearch.update');
        Route::post('/adminPortal/application', AdminPortalApplicationController::class)
            ->name('admin.portal.application.grant');
        Route::post('/users/activateChannel', AdminPortalActivateChannelController::class)
            ->name('channels.activate');
    });
});
//https://www.sso.uni-erlangen.de/simplesaml/saml2/idp/metadata.php
//redirect the saml2 logged-in user to previous page e.g. a clip with portal acl
Route::get('/saml2Login', function () {
    $redirectUrl = (session()->has('url.intended')) ? session('url.intended') : RouteServiceProvider::HOME;

    return redirect($redirectUrl);
})->name('saml2.redirect');
Route::get('/test/{series}/elk', function (Series $series, OpenSearchService $elkService) {
    $elkService->createIndex($series);
})->name('opensearch.test');

require __DIR__.'/auth.php';
