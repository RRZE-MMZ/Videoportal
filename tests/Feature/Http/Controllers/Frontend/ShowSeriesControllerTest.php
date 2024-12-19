<?php

use App\Enums\Acl;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\Chapter;
use App\Models\Clip;
use App\Models\Presenter;
use App\Models\Series;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Support\Carbon;

use function Pest\Laravel\get;

uses()->group('frontend');

it('shows all available series on series index page', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    get(route('frontend.series.index'))->assertSee($series->title);
});

test(' series route working also with course urls keeping backwards compatibility', function () {
    $series = Series::factory()->create();

    get('/course/id/'.$series->id)->assertRedirectToRoute('frontend.series.show', $series);
});

it('lists a livestream clip to visitors', function () {
    $series = SeriesFactory::withClips(1)->create();
    $clip = $series->clips()->first();

    $clip->is_livestream = true;
    $clip->save();

    $series->refresh();
    get(route('frontend.series.show', $series))->assertSee($clip->title);
});

it('lists all clips with media assets to visitors', function () {
    $series = SeriesFactory::withClips(2)->withAssets(2)->create();
    $clipWithoutAsset = Clip::factory()->create([
        'series_id' => $series->id,
        'recording_date' => Carbon::now()->addDays(2),
    ]);

    get(route('frontend.series.show', $series))
        ->assertSee($series->first()->title)
        ->assertDontSee($clipWithoutAsset->title);
});

it('lists also clips without media assets but they are in the past', function () {
    $series = SeriesFactory::withClips(2)->withAssets(2)->create();
    $clipWithoutAssetInThePast = Clip::factory()->create([
        'series_id' => $series->id,
        'recording_date' => Carbon::now()->subDays(2),
    ]);
    $clipWithoutAssetInTheFuture = Clip::factory()->create([
        'series_id' => $series->id,
        'recording_date' => Carbon::now()->addDays(2),
    ]);

    get(route('frontend.series.show', $series))
        ->assertSee($series->first()->title)
        ->assertSee($clipWithoutAssetInThePast->title)
        ->assertDontSee($clipWithoutAssetInTheFuture->title);
});

it('shows a series public page even without clips to series owner', function () {
    $series = SeriesFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    get(route('frontend.series.show', $series))->assertOk();
});

it('shows a forbidden series page for series without clips to visitors', function () {
    get(route('frontend.series.show', SeriesFactory::create()))->assertForbidden();
});

it('shows an unauthorized page for non public series to visitors', function () {
    get(route('frontend.series.show', SeriesFactory::withClips(2)->withAssets(1)->notPublic()->create()))
        ->assertForbidden();
});

it('shows series public page if series is not public to series owner', function () {
    get(route(
        'frontend.series.show',
        SeriesFactory::ownedBy($this->signIn())->withClips(2)->withAssets(1)->notPublic()->create()
    ))
        ->assertOk();
});

it('shows series public page to portal admins', function () {
    signInRole(Role::ADMIN);
    get(route('frontend.series.show', SeriesFactory::withClips(2)->withAssets(1)->notPublic()->create()))
        ->assertOk();
});

test('series public page lists all public clips', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $firstClip = Clip::find(1);
    $secondClip = Clip::find(2);
    $firstClip->is_public = false;
    $firstClip->save();

    get(route('frontend.series.show', $series))
        ->assertSee($secondClip->title)
        ->assertDontSee($firstClip->title);
});

test('series public page lists all clips acls', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $series->clips->each(function ($clip) {
        $clip->addAcls(collect([Acl::PORTAL(), Acl::PASSWORD()]));
    });

    get(route('frontend.series.show', $series))
        ->assertSee(Acl::PORTAL->lower().','.Acl::PASSWORD->lower());
});

it('shows for each clip if it is locked or not', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $series->clips->each(function ($clip) {
        $clip->addAcls(collect([Acl::PORTAL()]));
    });

    get(route('frontend.series.show', $series))->assertSee(__('common.locked').' clip');

    signIn();

    get(route('frontend.series.show', $series))->assertSee(__('common.unlocked').' clip');
});

it('list all clips with semester info', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();

    get(route('frontend.series.show', $series))->assertSee($series->clips->first()->semester->name);
});

it('shows series semester info', function () {
    get(route('frontend.series.show', $series = SeriesFactory::withClips(2)->withAssets(1)->create()))
        ->assertSee($series->latestClip->semester->name);
});

it('has a series feed button', function () {
    get(route('frontend.series.show', SeriesFactory::withClips(2)->withAssets(1)->create()))
        ->assertSee('Feeds');
});

it('shows feed links for different formats', function () {
    $series = SeriesFactory::withClips(2)->withAssets(2)->create();
    $series->clips->first()->assets()->save(Asset::factory()->create(['width' => 640]));

    get(route('frontend.series.show', $series))
        ->assertSee('QHD')
        ->assertSee('SD');
});

it('shows series multiple semester info if has clips from multiple semesters', function () {

    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $firstClip = $series->clips()->first();
    $firstClip->semester_id = 2;
    $firstClip->save();

    get(route('frontend.series.show', $series))
        ->assertSee($series->latestClip->semester->name)
        ->assertSee($series->clips()->first()->semester->name);
});

it('shows series presenters', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $presenter = Presenter::factory()->create();

    $series->addPresenters(collect($presenter->id));

    get(route('frontend.series.show', $series))->assertSee($presenter->getFullNameAttribute());
});

it('shows a subscribe button for logged in users', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    get(route('frontend.series.show', $series))->assertDontSee('Subscribe');
    signin();

    get(route('frontend.series.show', $series))->assertSee('Subscribe');
});

it('shows an unlock button if series has password protection', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $series->clips->each(function ($clip) {
        $clip->addAcls(collect([Acl::PASSWORD()]));
    });

    get(route('frontend.series.show', $series))->assertSee('Unlock series');
});

it('hides unlock button to portal admins', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();
    $series->clips->each(function ($clip) {
        $clip->addAcls(collect([Acl::PASSWORD()]));
    });

    signInRole(Role::ADMIN);

    get(route('frontend.series.show', $series))->assertDontSee('Unlock series');
});

it('displays series chapters', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->withChapters(1)->create();

    get(route('frontend.series.show', $series))->assertSee($series->chapters()->first()->title);
});

it('will display chapters with clips that have assets and are public', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->withChapters(1)->create();
    Clip::factory()->create([
        'series_id' => $series,
        'chapter_id' => $newChapter = Chapter::factory()->create(['series_id' => $series])]);

    get(route('frontend.series.show', $series))
        ->assertSee($series->chapters()->first()->title)
        ->assertDontSee($newChapter->title);
});

it('will display chapters with livestream clips', function () {
    $series = SeriesFactory::withClips(1)->withChapters(1)->create();
    $clip = $series->clips()->first();
    $clip->is_livestream = true;
    $clip->save();

    get(route('frontend.series.show', $series))->assertSee($clip->title);
});
