<?php

use App\Enums\OpencastWorkflowState;
use App\Enums\Role;
use App\Models\Clip;
use App\Models\Livestream;
use App\Models\Stats\AssetViewCount;
use App\Services\OpencastService;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Setup\WorksWithOpencastClient;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses()->group('backend');
uses(WorksWithOpencastClient::class);

uses()->beforeEach(function () {
    signInRole(Role::MODERATOR);
    AssetViewCount::factory()->create();
});

uses()->afterEach(function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
});

it('should not be accessed by a visitor', function () {
    auth()->logout(); // sign out the logged in moderator from setUp

    get(route('dashboard'))->assertRedirectToRoute('login');
});

it('should not be accessed by a logged in user with role user', function () {
    auth()->logout(); // sign out the logged in moderator from setUp
    signInRole(Role::USER);

    get(route('dashboard'))->assertForbidden();
});

it('should not be accessed by a logged in user with role student ', function () {
    auth()->logout(); // sign out the logged in moderator from setUp
    signInRole(Role::STUDENT);

    get(route('dashboard'))->assertForbidden();
});

it('can be accessed by a logged in user with role moderator', function () {
    get(route('dashboard'))->assertOk();
});

it('should be display an add series button', function () {
    get(route('dashboard'))
        ->assertSee(route('series.create'))
        ->assertSee(__('dashboard.new series'));
});

it('should be display an add clip button', function () {
    get(route('dashboard'))
        ->assertSee(route('clips.create'))
        ->assertSee(__('dashboard.new clip'));
});

it('display\'s user series', function () {
    SeriesFactory::create();
    $series = SeriesFactory::ownedBy(auth()->user())->create();

    get(route('dashboard'))->assertSee($series->title);
});

it('display\'s user supervised series', function () {
    auth()->logout();
    $user = $this->signInRole(Role::SUPERADMIN);
    $series = SeriesFactory::withClips(1)->withAssets(4)->create();
    $series->clips()->first()->supervisor_id = $user->id;
    $series->clips()->first()->save();

    $this->get(route('dashboard'))->assertSee($series->title);
});

it('display\'s user clips', function () {
    ClipFactory::create();
    $userClip = ClipFactory::ownedBy(auth()->user())->create();

    get(route('dashboard'))->assertSee($userClip->title);
});

it('should list all dropzone files for portal administrators', function () {
    auth()->logout();
    signInRole(Role::ASSISTANT);

    Storage::fake('video_dropzone');
    Storage::disk('video_dropzone')->put('test.pdf', 'some non-pdf content');

    get(route('dashboard'))->assertSee('test.pdf');
});

it('should hide dropzone files for portal moderators', function () {
    Storage::fake('video_dropzone');
    Storage::disk('video_dropzone')->put('test.pdf', 'some non-pdf content');

    get(route('dashboard'))->assertDontSee('test.pdf');
});

it('shows sidebar menu items for moderators', function () {
    get(route('dashboard'))
        ->assertSee(route('series.index'))
        ->assertSee('Clips')
        ->assertSee('Podcasts')
        ->assertDontSee('Activities')
        ->assertDontSee('Opencast')
        ->assertDontSee('Users')
        ->assertSee('images');
});

it('shows sidebar menu items for portal assistants', function () {
    auth()->logout();
    signInRole(Role::ASSISTANT);

    get(route('dashboard'))
        ->assertSee(trans_choice('common.menu.activity', 2))
        ->assertSee(route('activities.index'))
        ->assertSee(trans_choice('common.menu.device', 2))
        ->assertSee(route('devices.index'))
        ->assertSee('Livestreams')
        ->assertSee(route('livestreams.index'));
});

it('shows sidebar menu items for admins', function () {
    auth()->logout();
    signInRole(Role::ADMIN);

    get(route('dashboard'))
        ->assertSee(trans_choice('common.menu.device', 2))
        ->assertSee(route('devices.index'))
        ->assertSee(trans_choice('common.menu.collection', 2))
        ->assertSee(route('collections.index'))
        ->assertSee(trans_choice('common.menu.user', 2))
        ->assertSee(route('users.index'))
        ->assertSee(trans_choice('common.menu.image', 2))
        ->assertSee(route('articles.index'))
        ->assertSee(trans_choice('common.menu.article', 2));
});

it('shows sidebar menu items for superadmins', function () {
    auth()->logout();
    signInRole(Role::SUPERADMIN);

    get(route('dashboard'))
        ->assertSee(route('systems.status'))
        ->assertSee(__('common.menu.portal settings'));
});

it('hides the go to series or clip inputs for moderators', function () {
    get(route('dashboard'))
        ->assertDontSee('Go to')
        ->assertDontSee(route('goto.series'))
        ->assertDontSee('Series ID')
        ->assertDontSee(route('goto.clip'))
        ->assertDontSee('Clip ID');
});

it('redirects to dashboard if go to series/clip doesn\'t exist', function () {
    post(route('goto.series', ['seriesID' => 100]))
        ->assertRedirect(route('dashboard'));
    post(route('goto.clip', ['clipID' => 100]))
        ->assertRedirect(route('dashboard'));
});

it('should display Opencast running events if any', function () {
    auth()->logout();
    $series = SeriesFactory::withOpencastID()->create();
    app(OpencastService::class);
    $mockHandler = $this->swapOpencastClient();
    $mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockEventResponse($series, OpencastWorkflowState::RECORDING, 2),
        $this->mockEventResponse($series, OpencastWorkflowState::RUNNING, 2),
        $this->mockEventResponse($series, OpencastWorkflowState::SCHEDULED, 2),
        $this->mockEventResponse($series, OpencastWorkflowState::FAILED, 2),
        $this->mockTrimmingEventsResponse($series),
    );
    signInRole(Role::ADMIN);

    get(route('dashboard'))->assertSee(__('opencast.backend.running events', ['counter' => 1]));
});

it('should display Opencast recording events if any', function () {
    auth()->logout();
    $series = SeriesFactory::withOpencastID()->create();
    app(OpencastService::class);
    $mockHandler = $this->swapOpencastClient();
    $mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockEventResponse($series, OpencastWorkflowState::RECORDING, 2),
        $this->mockEventResponse($series, OpencastWorkflowState::RUNNING, 2),
        $this->mockEventResponse($series, OpencastWorkflowState::SCHEDULED, 2),
        $this->mockEventResponse($series, OpencastWorkflowState::FAILED, 2),
        $this->mockNoTrimmingResultsResponse(),
    );
    signInRole(Role::ADMIN);

    get(route('dashboard'))->assertSee(__('opencast.backend.recording events', ['counter' => 1]));
});

it('hides Opencast running events for moderators', function () {
    $series = SeriesFactory::withOpencastID()->create();
    app(OpencastService::class);
    $mockHandler = $this->swapOpencastClient();
    $mockHandler->append($this->mockEventResponse($series, OpencastWorkflowState::RUNNING, 2));

    get(route('dashboard'))->assertDontSee(__('opencast.backend.running events', ['counter' => 1]))->assertOk();
});

it('should not display any Opencast information if opencast server is not available', function () {
    app(OpencastService::class);
    $mockHandler = $this->swapOpencastClient();
    $mockHandler->append($this->mockServerNotAvailable());

    signInRole(Role::ADMIN);

    get(route('dashboard'))->assertViewHas(['opencastEvents' => collect()]);
});

it('should display scheduled events for a moderators series', function () {
    $series = SeriesFactory::ownedBy(auth()->user())->withOpencastID()->create();

    app(OpencastService::class);
    $mockHandler = $this->swapOpencastClient();
    $mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockNoResultsResponse(),
        $this->mockEventResponse($series, OpencastWorkflowState::RUNNING, 2),
        $this->mockNoResultsResponse(),
        $this->mockNoResultsResponse(),
        $this->mockNoTrimmingResultsResponse(),
        $this->mockNoResultsResponse(),
    );

    get(route('dashboard'))->assertSee(__('opencast.backend.running events', ['counter' => 1]))->assertOk();
});

it('hides opencast events for unauthorized moderators', function () {
    $series = SeriesFactory::ownedBy(auth()->user())->withOpencastID()->create();
    auth()->logout();

    // sign in another Moderator
    signInRole(Role::MODERATOR);

    app(OpencastService::class);
    $mockHandler = $this->swapOpencastClient();
    $mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockNoResultsResponse(),
        $this->mockEventResponse($series, OpencastWorkflowState::RUNNING, 2),
        $this->mockNoResultsResponse(),
        $this->mockNoResultsResponse(),
        $this->mockNoTrimmingResultsResponse(),
    );

    get(route('dashboard'))->assertDontSee(__('opencast.backend.running events', ['counter' => 1]))->assertOk();
});

it('should display all active livestreams for portal admins', function () {
    auth()->logout();
    $clip = Clip::factory()->create(['is_livestream' => true]);
    $this->signInRole(Role::ADMIN);
    $activeLivestream = Livestream::factory()->create(['clip_id' => $clip->id, 'active' => true]);

    get(route('dashboard'))->assertSee($activeLivestream->name);
});
