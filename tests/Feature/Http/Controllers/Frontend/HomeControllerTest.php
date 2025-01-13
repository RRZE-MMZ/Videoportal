<?php

use App\Enums\Acl;
use App\Enums\Role;
use App\Models\Clip;
use App\Models\Series;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

uses()->group('frontend');

uses(WithFaker::class);

test('should show project name', function () {
    get(route('home'))->assertSee(env('APP_NAME'));
});

it('has a language switcher', function () {
    get(route('home'))->assertSee('EN')->assertSee('DE');
});

it('has a channels top menu item', function () {
    $menuItem = '<a href="'.route('frontend.channels.index').'"';

    get(route('home'))->assertSee($menuItem, false);
});
it('has a series top menu item', function () {
    $menuItem = '<a href="'.route('frontend.series.index').'"';

    get(route('home'))->assertSee($menuItem, false);
});

it('has a clips top menu item', function () {
    $menuItem = '<a href="'.route('frontend.clips.index').'"';

    get(route('home'))->assertSee($menuItem, false);
});

it('has a faculties top menu item', function () {
    $menuItem = '<a href="'.route('frontend.organizations.index').'"';

    get(route('home'))->assertSee($menuItem, false);
});

it('has a live now menu item,', function () {
    $menuItem = '<a href="'.route('frontend.livestreams.index').'"';

    get(route('home'))->assertSee($menuItem, false);
});

it('has an faq menu item,', function () {
    $menuItem = '<a href="'.route('frontend.faq').'"';

    get(route('home'))->assertSee($menuItem, false);
});

it('has a contact menu item,', function () {
    $menuItem = '<a href="'.route('frontend.contact').'"';

    get(route('home'))->assertSee($menuItem, false);
});

it('has an imprint menu item,', function () {
    $menuItem = '<a href="'.route('frontend.contact').'">';

    get(route('home'))->assertSee($menuItem, false);
});

it('has a privacy menu item,', function () {
    $menuItem = '<a href="'.route('frontend.privacy').'">';

    get(route('home'))->assertSee($menuItem, false);
});

it('has an accessibility menu item,', function () {
    $menuItem = '<a href="'.route('frontend.accessibility').'">';

    get(route('home'))->assertSee($menuItem, false);
});

it('changes portal language', function () {
    followingRedirects()->get('/set_lang/de');

    get(route('home'))->assertSee('Letzte Videoaufnahmen');
});

it('does not display series with clips without assets', function () {
    $series = SeriesFactory::withClips(1)->create();

    get(route('home'))->assertDontSee($series->title);
});

it('displays latest series with clips that are open and have assets to visitors', function () {
    $series = SeriesFactory::create();
    $clip = ClipFactory::withAssets(1)->create();
    $clip->addAcls(collect(Acl::PUBLIC()));
    $series->clips()->save($clip);

    get(route('home'))->assertSee(Str::limit($series->title, 100, preserveWords: true));
});

it('displays latest series with clips that are portal protected to logged in users', function () {
    $series = SeriesFactory::create();
    $clip = ClipFactory::withAssets(1)->create();
    $clip->addAcls(collect(Acl::PORTAL()));
    $series->clips()->save($clip);

    get(route('home'))->assertDontSee(Str::limit($series->title, 100, preserveWords: true));

    signInRole(Role::STUDENT);
    get(route('home'))->assertSee(Str::limit($series->title, 100, preserveWords: true));
});

it('should not display series that is not public', function () {
    $series = SeriesFactory::create();
    $clip = ClipFactory::withAssets(1)->create();
    $clip->addAcls(collect(Acl::PUBLIC()));
    $series->clips()->save($clip);

    get(route('home'))->assertSee(Str::limit($series->title, 100, preserveWords: true));

    $series->is_public = false;
    $series->save();

    get(route('home'))->assertDontSee(Str::limit($series->title, 100, preserveWords: true));
});

it('does not display clips without assets', function () {
    $clip = ClipFactory::create();

    get(route('home'))->assertDontSee(Str::limit($clip->title, 100, preserveWords: true));
});

it('does not display clips that belong to a series', function () {
    $series = SeriesFactory::withClips(1)->create();

    get(route('home'))->assertDontSee(Str::limit($series->clips()->first()->title, 20, '...'));
});

it('displays clips with video assets', function () {
    $clip = ClipFactory::withAssets(1)->create();

    get(route('home'))->assertSee(Str::limit($clip->title, 100, preserveWords: true));
});

it('should not display clips that are not public', function () {
    $clip = ClipFactory::withAssets(1)->create();

    get(route('home'))->assertSee(Str::limit($clip->title, 100, preserveWords: true));

    $clip->is_public = false;
    $clip->save();

    get(route('home'))->assertDontSee(Str::limit($clip->title, 100, preserveWords: true));
});

it('shows dashboard menu item for admins', function () {
    signInRole(Role::ADMIN);

    get(route('home'))->assertSee('Dashboard');
});

it('shows dashboard menu item for moderators', function () {
    signInRole(Role::MODERATOR);

    get(route('home'))->assertSee('Dashboard');
});

it('shows dashboard menu item for assistants', function () {
    signInRole(Role::ASSISTANT);

    get(route('home'))->assertSee('Dashboard');
});

it('show an hide logged in user series subscriptions', function () {
    signIn();

    $userSettings = auth()->user()->settings;

    SeriesFactory::withClips(2)->withAssets(2)->create(10);

    $this->assertDatabaseHas('settings', [
        'name' => auth()->user()->username,
        'data' => json_encode(config('settings.user')), ]);

    acceptUseTerms();

    $userSettings->refresh();

    get(route('home'))->assertDontSee('Your Series subscriptions');

    $attributes = [
        'language' => 'en',
        'show_subscriptions_to_home_page' => 'on',
    ];

    put(route('frontend.userSettings.update'), $attributes);

    get(route('home'))
        ->assertSee(__('homepage.series.Your series subscriptions'))
        ->assertSee(__('homepage.series.You are not subscribed to any series'));

    auth()->user()->subscriptions()->attach([
        Series::find(3)->id, Series::find(4)->id,
    ]);

    auth()->user()->refresh();

    get(route('home'))
        ->assertSee(__('homepage.series.Your series subscriptions'))
        ->assertDontSee(__('homepage.series.You are not subscribed to any series'));
});

it('hide non visible clip acls in series description', function () {
    $series = SeriesFactory::withClips(2)->withAssets(1)->create();

    $firstClip = Clip::find(1);
    $secondClip = Clip::find(2);

    $firstClip->addAcls(collect([Acl::PORTAL()]));
    $secondClip->addAcls(collect([Acl::PORTAL()]));

    signInRole(Role::STUDENT);
    get(route('home'))->assertSee('portal');

    // clip has no assets thus should not be displayed to visitors
    $thirdClip = Clip::factory()->create(['series_id' => $series]);

    $thirdClip->addAcls(collect([Acl::LMS()]));

    get(route('home'))->assertDontSee('portal, lms');
});

it('has an faq static page,', function () {
    get(route('frontend.faq'))->assertOk();
});

it('has a contact static page,', function () {
    get(route('frontend.contact'))->assertOk();
});
