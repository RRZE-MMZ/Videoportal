<?php

use App\Enums\Acl;
use App\Enums\OpencastWorkflowState;
use App\Enums\Role;
use App\Events\SeriesTitleUpdated;
use App\Livewire\ActivitiesDataTable;
use App\Livewire\CommentsSection;
use App\Models\Clip;
use App\Models\Image;
use App\Models\Series;
use App\Models\User;
use App\Services\OpencastService;
use Facades\Tests\Setup\SeriesFactory;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\Setup\WorksWithOpencastClient;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses()->group('backend');
uses(WithFaker::class);
uses(WorksWithOpencastClient::class);

beforeEach(function () {
    $this->mockHandler = $this->swapOpencastClient();
    $this->opencastService = app(OpencastService::class);
    $this->flashMessageName = 'flashMessage';
});

it('denies series management to visitors', function () {
    post(route('series.store'), [])->assertRedirect('login');
});

it('shows a create series button if moderator has no series', function () {
    signInRole(Role::MODERATOR);

    get(route('series.index'))->assertSee(__('series.backend.actions.create series'));
});

it('shows series information in index page', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))
        ->withClips(3)
        ->withAssets(2)
        ->create();
    Clip::find(1)->addAcls(collect([Acl::PORTAL()]));
    //assign 'intern' acl
    Clip::find(2)->addAcls(collect([Acl::LMS()]));
    //assign 'lms' acl
    Clip::find(3)->addAcls(collect([Acl::LMS()]));

    //assign 'lms' acl
    get(route('series.index'))
        ->assertSee($series->title)
        ->assertSee('portal, lms');
});

test('a moderator can see the create series form and all form fields', function () {
    signInRole(Role::MODERATOR);

    get(route('series.create'))
        ->assertSee('title')
        ->assertSee('description')
        ->assertSee('presenters')
        ->assertSee('acls')
        ->assertSee('password')
        ->assertSee('is_public')
        ->assertSee('Organization');

    get(route('series.create'))->assertOk()
        ->assertViewIs('backend.series.create');
});

it('requires a title when creating a new series', function () {
    signInRole(Role::MODERATOR);
    $attributes = Series::factory()->raw(['title' => '']);

    post(route('series.store'), $attributes)->assertSessionHasErrors('title');
});

it('requires an organization id when creating a new series', function () {
    signInRole(Role::MODERATOR);
    $attributes = Series::factory()->raw(['organization_id' => null]);

    post(route('series.store'), $attributes)->assertSessionHasErrors('organization_id');
});

it('must have a strong password if any', function () {
    signInRole(Role::MODERATOR);

    post(route('series.store', Series::factory()->raw([
        'title' => 'This is a test',
        'password' => '1234',
        'organization_id' => '1',
    ])))->assertSessionHasErrors('password');

    post(route('series.store', Series::factory()->raw([
        'title' => 'This is a test',
        'password' => '1234qwER',
        'organization_id' => '1',
    ])));

    assertDatabaseHas('series', ['password' => '1234qwER']);
});

test('an authenticated user is not allowed to create new series', function () {
    $this->signIn();

    post(
        route('series.store'),
        [
            'title' => 'Test title',
            'description' => 'Test description',
            'organization_id' => '1',
        ]
    )->assertForbidden();
});

test('an assistant is not allowed to create new series', function () {
    signInRole(Role::ASSISTANT);
    $this->mockHandler->append($this->mockCreateSeriesResponse());

    post(
        route('series.store'),
        [
            'title' => 'Test title',
            'description' => 'Test description',
            'organization_id' => '1',
            'image_id' => config('settings.portal.default_image_id'),
        ]
    )->assertForbidden();
});

test('a moderator can create a series', function () {
    signInRole(Role::MODERATOR);

    post(
        route('series.store'),
        [
            'title' => 'Test title',
            'description' => 'Test description',
            'organization_id' => '1',
            'image_id' => config('settings.portal.default_image_id'),
        ]
    );

    assertDatabaseHas('series', ['title' => 'Test title']);
});

test('an admin can create a series', function () {
    signInRole(Role::ADMIN);

    post(
        route('series.store'),
        [
            'title' => 'Test title',
            'description' => 'Test description',
            'organization_id' => '1',
            'image_id' => config('settings.portal.default_image_id'),
        ]
    );

    assertDatabaseHas('series', ['title' => 'Test title']);
});

it('shows a flash message when a series is created', function () {
    signInRole(Role::MODERATOR);

    post(
        route('series.store'),
        [
            'title' => 'Test title',
            'description' => 'Test description',
            'organization_id' => '1',
        ]
    )->assertSessionHas($this->flashMessageName);
});

it('creates an opencast series when new series is created', function () {
    signInRole(Role::MODERATOR);
    $this->mockHandler->append($this->mockCreateSeriesResponse());
    post(route('series.store'), [
        'title' => 'Series title',
        'description' => 'test',
        'organization_id' => '1',
        'image_id' => config('settings.portal.default_image_id'),
    ]);
    $series = Series::all()->first();

    expect($series->opencast_series_id)->not->toBeNull();
});

it('requires a title creating a series', function () {
    signInRole(Role::MODERATOR);
    $attributes = Series::factory()->raw(['title' => '']);

    post(route('series.store'), $attributes)->assertSessionHasErrors('title');
});

test('create series form should remember old values on validation error', function () {
    signInRole(Role::MODERATOR);
    $attributes = [
        'title' => '',
        'description' => 'test',
    ];
    post(route('series.store'), $attributes);
    followingRedirects();

    get(route('series.create'))->assertSee($attributes);
});

test('a series owner can view edit form fields', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))
        ->assertOk()
        ->assertViewIs('backend.series.edit')
        ->assertSee('title')
        ->assertSee('presenters')
        ->assertSee('description');
});

test('a series member can view edit form fields and owner name and username', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    auth()->logout();
    $user2 = signInRole(Role::MODERATOR);

    get(route('series.edit', $series))->assertForbidden();

    $series->addMember($user2);
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))
        ->assertOk()
        ->assertSee($series->owner->username);
});

test('a moderator cannot view edit clip form for not owned series', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    $series = SeriesFactory::create();
    signInRole(Role::MODERATOR);

    get(route('series.edit', $series))->assertForbidden();
});

test('an admin can edit a not owned series', function () {
    $series = SeriesFactory::create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );
    signInRole(Role::ADMIN);

    get(route('series.edit', $series))->assertOk();
});

test('a superadmin can edit a not owned series', function () {
    $series = SeriesFactory::create();

    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    signInRole(Role::SUPERADMIN);

    get(route('series.edit', $series))->assertOk();
});

it('has an add clips button', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    get(route('series.edit', SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create()))
        ->assertSee(__('series.backend.actions.add new clip'));
});

it('has go to public page button', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    get(route('series.edit', SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create()))
        ->assertSee(__('series.backend.actions.go to public page'));
});

it('has a series statistics button', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    get(route('series.edit', $series))
        ->assertSee(trans_choice('common.forms.statistic', 2))->assertSee(route('statistics.series', $series));
});

test('edit series page should display belonging clips', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->withClips(2)->create();

    get(route('series.edit', $series))->assertSee($series->clips()->first()->title);
});

test('edit series should display series image information', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.edit', $series))
        ->assertSee($series->image->description);
});

test('edit series page should display all series buttons for actions', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->withClips(3)->create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );
    get(route('series.edit', $series))
        ->assertSee(route('series.clips.create', $series))
        ->assertSee(route('frontend.series.show', $series))
        ->assertSee(route('series.clips.reorder', $series))
        ->assertSee(route('series.clips.batch.show.clips.metadata', $series))
        ->assertSee(route('series.chapters.index', $series))
        ->assertSee(route('series.destroy', $series));
});

test('edit series page should hide some action buttons if series has no clips', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );
    get(route('series.edit', $series))
        ->assertSee(route('series.clips.create', $series))
        ->assertSee(route('frontend.series.show', $series))
        ->assertDontSee(route('series.clips.reorder', $series))
        ->assertDontSee(route('series.clips.batch.show.clips.metadata', $series))
        ->assertSee(route('series.chapters.index', $series))
        ->assertSee(route('series.destroy', $series));
});

test('edit series should allow user to switch to default image if one is set', function () {
    Image::factory(2)->create();
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $series->image_id = Image::find(2)->id;
    $series->save();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))
        ->assertSee(__('image.backend.Set default image'));

    $series->image_id = 1;
    $series->save();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))
        ->assertDontSee('Set Default image');
});

test('series admin can select another image', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $series->image_id = Image::find(1)->id;
    $series->save();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))
        ->assertSee(__('image.backend.Assign selected image'));
});

test('edit series page should display opencast users rights', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );
    get(route('series.edit', $series))
        ->assertViewHas(['opencastSeriesInfo'])
        ->assertSee(User::find(1)->first()->getFullNameAttribute());
});

test('edit series should display opencast running events if any', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), // seriesThemes
        $this->mockNoResultsResponse(), //recording
        $runningWorkflow = $this->mockEventResponse($series, OpencastWorkflowState::RUNNING), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );
    $opencastViewData = collect(json_decode($runningWorkflow->getBody(), true));

    get(route('series.edit', $series))
        ->assertViewHas(['opencastSeriesInfo'])
        ->assertSee($opencastViewData[0]['title']);
});

test('edit series should display opencast failed events if any', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))
        ->withOpencastID()
        ->create();
    //pass an empty opencast response
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), // seriesThemes
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockEventResponse($series, OpencastWorkflowState::FAILED), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))
        ->assertViewHas(['opencastSeriesInfo'])
        ->assertSee(__('opencast.backend.failed events', ['counter' => 1]));
});

it('loads comments component at edit page', function () {
    $this->mockHandler->append(new Response);
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.edit', $series))->assertSeeLivewire('comments-section');
});

test('edit series should display admin comments', function () {
    $this->mockHandler->append(new Response);
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.edit', $series))->assertSee(__('clip.frontend.comments'));

    Livewire::test(CommentsSection::class, [
        'model' => $series,
        'type' => 'backend',
    ])
        ->set('content', 'Admin series comment')
        ->call('postComment')
        ->assertSee('Comment posted successfully')
        ->assertSee('Admin series comment');
});

test('edit series should display series activities', function () {
    $this->mockHandler->append(new Response);
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.edit', $series))->assertSee(trans_choice('common.menu.activity', 2));

    Livewire::test(ActivitiesDataTable::class)
        ->assertSee('created series');
});

test('a series owner can update series', function () {
    $this->mockHandler->append(new Response);
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $this->patch(route('series.edit', $series), [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ]);
    $series->refresh();

    assertDatabaseHas('series', [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ]);
    get(route('series.edit', $series))->assertSee('changed');
});

it('updates opencast series id if is null', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    //pass an empty opencast response
    $this->mockHandler->append($this->mockCreateSeriesResponse());
    $this->patch(route('series.edit', $series), [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ]);
    $series = $series->refresh();

    expect($series->opencast_series_id)->not->toBeNull();
});

it('it updates opencast series title when title is changed', function () {
    Event::fake([
        SeriesTitleUpdated::class,
    ]);
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $this->mockHandler->append($this->mockCreateSeriesResponse());
    $this->patch(route('series.edit', $series), [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ]);
    Event::assertDispatched(SeriesTitleUpdated::class);

});

it('shows create oc series button if no series exist in opencast for portal admins', function () {

    $series = SeriesFactory::create();
    signInRole(Role::ASSISTANT);
    $this->mockHandler->append(
        $this->mockHealthResponse(), //health
        $this->mockNoResultsResponse(), // seriesInfo
        $this->mockNoResultsResponse(), //recording
        $this->mockNoResultsResponse(), //running
        $this->mockNoResultsResponse(), //scheduled
        $this->mockNoResultsResponse(), //failed
        $this->mockNoTrimmingResultsResponse(), //trimming
        $this->mockNoResultsResponse(), //upcoming
    );

    get(route('series.edit', $series))->assertSee('Create Opencast series for this object');
});

test('a moderator cannot update a not owned series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::MODERATOR);

    $this->patch(route('series.edit', $series), [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ])->assertForbidden();
    $this->assertDatabaseMissing('series', ['title' => 'changed']);
});

test('an admin user can update a not owned series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::ADMIN);
    //pass an empty opencast respons
    $this->mockHandler->append($this->mockSeriesRunningWorkflowsResponse($series, false));
    $this->patch(route('series.edit', $series), [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ]);

    assertDatabaseHas('series', ['title' => 'changed']);
});

it('shows a flash message when a series is updated', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    $this->patch(route('series.edit', $series), [
        'title' => 'changed',
        'description' => 'changed',
        'organization_id' => '1',
    ])->assertSessionHas($this->flashMessageName);
});

test('a moderator cannot delete a not owned series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::MODERATOR);

    delete(route('series.edit', $series))->assertForbidden();
    assertDatabaseHas('series', $series->only('id'));
});

test('an assistant is not allowed to delete series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::ASSISTANT);

    delete(route('series.edit', $series))->assertForbidden();
    assertDatabaseHas('series', $series->only('id'));
});

test('an admin user can delete a not owned series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::ADMIN);

    $this->followingRedirects()->delete(route('series.edit', $series))->assertOk();
    $this->assertDatabaseMissing('series', $series->only('id'));
});

test('a series owner can delete series', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    delete(route('series.edit', $series));

    $this->assertDatabaseMissing('series', $series->only('id'));
});

it('shows a flash message when a series is deleted', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    delete(route('series.edit', $series))->assertSessionHas($this->flashMessageName);
});

it('shows series owner if user is admin', function () {
    $series = Series::factory()->create();
    signInRole(Role::ADMIN);

    get(route('series.edit', $series))->assertSee($series->owner->first_name);
});

afterEach(function () {
    // TODO: Change the autogenerated stub
});
