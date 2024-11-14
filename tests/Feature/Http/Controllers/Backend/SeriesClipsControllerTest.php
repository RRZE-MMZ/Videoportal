<?php

use App\Enums\Role;
use App\Http\Controllers\Backend\SeriesController;
use App\Models\Clip;
use App\Models\User;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\SeriesFactory;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

covers(SeriesController::class);
uses()->group('backend');

it('redirects a non logged in user if tried to add a clip to a series', function () {
    get(route('series.clips.create', SeriesFactory::create()))->assertRedirect();
});

it('shows a http forbidden page if a moderator tries to add a clip to a non owned series', function () {
    signInRole(Role::MODERATOR);

    get(route('series.clips.create', SeriesFactory::create()))->assertForbidden();
});

test('a series owner can add a clip to a series', function () {
    get(route('series.clips.create', SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create()))->assertOk();
});

test('a series member can add a clip to a series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::MODERATOR);

    get(route('series.clips.create', $series))->assertForbidden();

    $series->addMember(auth()->user());

    get(route('series.clips.create', $series))->assertOk();
});

test('an admin can view and add a clip to a not owned series ', function () {
    signInRole(Role::ADMIN);

    get(route('series.clips.create', SeriesFactory::create()))->assertOk();
});

test('a series owner can view the form for adding a clip to a series', function () {
    get(route('series.clips.create', SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create()))
        ->assertSee('title')
        ->assertSee('recording_date')
        ->assertSee('description')
        ->assertSee('organization')
        ->assertSee('language')
        ->assertSee('context')
        ->assertSee('format')
        ->assertSee('type')
        ->assertSee('presenters')
        ->assertSee('tags')
        ->assertSee('acls')
        ->assertSee('semester')
        ->assertSee('is_public');
});

test('a series owner can select a chapter in adding clip to series form', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->withClips(2)->withChapters(1)->create();

    get(route('series.clips.create', $series))->assertSee('chapter');
});
test('a series owner can add clip to series', function () {
    post(
        route('series.clips.store', $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create()),
        Clip::factory()->raw()
    )->assertRedirect();

    get(route('clips.edit', $series->clips()->first()))->assertOk();

    expect($series->clips()->count())->toBe(1);
});

test('a series member can add clip to series', function () {
    $series = SeriesFactory::create();
    $user = $series->addMember(User::factory()->create()->assignRole(Role::MODERATOR));
    signIn($user);

    post(route('series.clips.store', $series), Clip::factory()->raw());

    expect($series->clips()->count())->toBe(1);
});

test('a portal admin can add clip to series', function () {
    $series = SeriesFactory::create();
    signInRole(Role::ADMIN);
    post(route('series.clips.store', $series), Clip::factory()->raw());

    expect($series->clips()->count())->toBe(1);
});

it('copies the metadata from the last series clip if any', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    $clip = Clip::factory()->create(['series_id' => $series->id]);
    expect($clip->id)->toBe($series->latestClip->id);
    $formatIDItem = '<option value="'.$clip->format_id.'" selected>';
    $typeIDItem = '<option value="'.$clip->type_id.'" selected>';
    $contextIDItem = '<option value="'.$clip->context_id.'" selected>';
    $organizationIDItem = '<option value="'.$clip->organization_id.'" selected>';

    get(route('series.clips.create', $series))
        ->assertSee($clip->title)
        ->assertSee($formatIDItem, false)
        ->assertSee($typeIDItem, false)
        ->assertSee($contextIDItem, false)
        ->assertSee($organizationIDItem, false);
});

it('has a view for selecting a series for a single clip', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $series = SeriesFactory::ownedBy($clip->owner)->create();

    get(route('series.clips.listSeries', $clip))
        ->assertViewIs('backend.seriesClips.listSeries')
        ->assertSee($series->title);
});

it('lists only series belonging to the moderator for assigning a single clip to a series', function () {
    $series = SeriesFactory::create();
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.clips.listSeries', $clip))->assertDontSee($series->title);
});

it('lists series that user has access to for assigning a single clip to a series', function () {
    $series = SeriesFactory::create();
    $user = signInRole(Role::MODERATOR);
    $singleClip = ClipFactory::ownedBy($user)->create();

    get(route('series.clips.listSeries', $singleClip))
        ->assertSee(__('series.backend.no user series found'));

    $series->addMember($user);

    get(route('series.clips.listSeries', $singleClip))->assertSee($series->title);
});

it('assigns a clip to a series', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    $series = SeriesFactory::withClips(2)->ownedBy($clip->owner)->create();
    post(route('series.clips.assign', compact('series', 'clip')))->assertRedirect();
    $clip->refresh();

    expect($clip->series_id)->toBe($series->id);
    expect($clip->episode)->toBe(3);
});

test('a moderator is not allowed to remove a clip from a not owned series', function () {
    $series = SeriesFactory::withClips(3)->create();
    signInRole(Role::MODERATOR);

    delete(route('series.clips.remove', $series->clips()->first()))->assertForbidden();
});

it('removes a clip from series', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();
    $clip = $series->clips()->first();

    delete(route('series.clips.remove', $clip))->assertRedirect();
    expect($series->clips()->count())->toBe(2);
});

it('shows a reorder clips button in series page', function () {
    get(route('series.edit', SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create()))
        ->assertSee(__('series.backend.actions.reorder series clips'));
});

it('has a view for reordering series clips based on clip episode', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.clips.changeEpisode', $series))
        ->assertSee($series->title)
        ->assertSee($series->clips()->first()->title)
        ->assertSee($series->latestClip->title);
});

it('validates an array of integers for changing clips episodes', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();

    post(route('series.clips.reorder', $series), ['episodes' => []])->assertSessionHasErrors('episodes');

    post(route('series.clips.reorder', $series), [
        'episodes' => [
            1 => 'asdfasdfasdf',
            2 => '1',
            3 => '2',
        ],
    ])->assertSessionHasErrors('episodes.*');

    post(route('series.clips.reorder', $series), [
        'episodes' => [
            1 => '3',
            2 => '1',
            3 => '2',
        ],
    ])->assertSessionHasNoErrors();
});

it('changes clips episodes for a series', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();

    post(route('series.clips.reorder', $series), [
        'episodes' => [
            1 => '3',
            2 => '1',
            3 => '2',
        ],
    ]);

    expect(Clip::find(1)->episode)->toBe(3);
    expect(Clip::find(2)->episode)->toBe(1);
    expect(Clip::find(3)->episode)->toBe(2);
});

test('mass update clips metadata form is available only to series owner ', function () {
    $series = SeriesFactory::withClips(3)->create();
    signInRole(Role::MODERATOR);

    get(route('series.clips.batch.show.clips.metadata', $series))->assertForbidden();

    auth()->logout();
    signInRole(Role::STUDENT);

    get(route('series.clips.batch.show.clips.metadata', $series))->assertForbidden();
});

test('mass update clips metadata form is also available to series members', function () {
    $series = SeriesFactory::withClips(3)->create();
    $user = signInRole(Role::MODERATOR);
    $series->addMember($user);

    get(route('series.clips.batch.show.clips.metadata', $series))->assertOk();
});

it('shows a clip metadata form and all clips for a certain series', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();

    get(route('series.clips.batch.show.clips.metadata', $series))->assertOk();
});

it('validates mass updates all series clips metadata', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();

    patch(route('series.clips.batch.update.clips.metadata', $series), [])->assertSessionHasErrors();
});

it('updates all clips metadata for a certain series', function () {
    $series = SeriesFactory::withClips(3)->ownedBy(signInRole(Role::MODERATOR))->create();
    $lastClip = $series->latestClip()->first();
    $attributes = [
        'title' => 'Series title',
        'organization_id' => '1',
        'language_id' => '1',
        'context_id' => '1',
        'format_id' => '1',
        'type_id' => '1',
        'semester_id' => '1',
    ];
    patch(route('series.clips.batch.update.clips.metadata', $series), $attributes)->assertSessionDoesntHaveErrors();

    expect($lastClip->title)->not->toBe($series->latestClip()->first()->title);
});
