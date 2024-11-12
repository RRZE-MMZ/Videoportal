<?php

use App\Enums\Acl;
use App\Enums\Content;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Presenter;
use App\Models\Setting;
use App\Models\User;
use App\Services\WowzaService;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\SeriesFactory;
use GuzzleHttp\Psr7\Response;
use Tests\Setup\WorksWithOpencastClient;
use Tests\Setup\WorksWithWowzaClient;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(
    WorksWithWowzaClient::class,
    WorksWithOpencastClient::class,
);

uses()->group('frontend');

beforeEach(function () {
    $this->clip = ClipFactory::withAssets(2)->create();
    $this->mockHandler = $this->swapWowzaClient();
    $this->wowzaService = app(WowzaService::class);
});

it('a guest cannot manage clips', function () {
    post(route('clips.store'), [])->assertRedirectToRoute('login');
    get(route('clips.create'))->assertRedirectToRoute('login');
    patch(route('clips.update', $this->clip))->assertRedirectToRoute('login');
    delete(route('clips.destroy', $this->clip))->assertRedirectToRoute('login');
});

it('a guest can view a clip page if clip has assets', function () {
    get(route('frontend.clips.show', $this->clip))->assertSee($this->clip->title);
});

it('a guest can view a livestream clip page if clip is a livestream', function () {
    $clip = ClipFactory::create();
    $clip->is_livestream = true;
    $clip->save();

    get(route('frontend.clips.show', $clip))->assertSee($this->clip->title);
});

it('clip url should also work with clip id', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    get(route('frontend.clips.show', $this->clip->id))->assertOk()->assertSee($this->clip->title);
});

it('a guest cannot access frontend clip page if clip is not public', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $this->clip->is_public = false;
    $this->clip->save();

    get(route('frontend.clips.show', $this->clip))->assertForbidden();
});

it('a guest cannot access frontend clip page if clip has no assets', function () {
    $this->mockHandler->append($this->mockCheckApiConnection());

    get(route('frontend.clips.show', ClipFactory::withAssets(0)->create()))->assertForbidden();
});

it('a logged in user cannot access frontend clip page if clip has no assets', function () {
    $this->mockHandler->append($this->mockCheckApiConnection());
    signIn();

    get(route('frontend.clips.show', ClipFactory::withAssets(0)->create()))->assertForbidden();
});

it('a clip owner can access frontend clip page if clip has no assets', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $emptyClip = ClipFactory::withAssets(0)->create();
    signIn($emptyClip->owner);

    $this->get(route('frontend.clips.show', $emptyClip))->assertOk();
});

it('a portal admin can access frontend clip page if clip has no assets', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    signInRole(Role::ADMIN);

    get(route('frontend.clips.show', ClipFactory::withAssets(0)->create()))->assertOk();
});

it('a clip owner can access frontend clip page if clip is not public visible', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $clip = ClipFactory::ownedBy(signIn())->create(['is_public' => false]);
    signIn($clip->owner);

    get(route('frontend.clips.show', $clip))->assertOk();
});

it('a portal admin can access frontend clip page if clip is not public visible', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $clip = ClipFactory::create(['is_public' => false]);
    signInRole(Role::ADMIN);

    get(route('frontend.clips.show', $clip))->assertOk();
});

it('a guest cannot access frontend clip page that belongs to series that it is not public', function () {
    $series = SeriesFactory::notPublic()->create();
    $this->clip->series_id = $series->id;
    $this->clip->save();

    get(route('frontend.clips.show', $this->clip))->assertForbidden();
});

it('a clip owner can access frontend clip page that belongs to series that it is not public ', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $user = signIn();
    $series = SeriesFactory::notPublic()->create();
    $this->clip->series_id = $series->id;
    $this->clip->owner_id = $user->id;
    $this->clip->save();

    get(route('frontend.clips.show', $this->clip))->assertOk();
});

it('player in clip public page is using wowza url if wowza server is available', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());

    get(route('frontend.clips.show', $this->clip))->assertSee(config('settings.streaming.wowza.server1.engine_url'));
});

it('player tries to load the video file as html5 source dom element', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());

    get(route('frontend.clips.show', $this->clip))->assertDontSee(env('WOWZA_ENGINE_URL'));
});

it('clip edit button in clip public page is hidden for guests', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    signIn();

    get(route('frontend.clips.show', $this->clip))->assertDontSee('Back to edit page');
});

it('clip comments in clip public page are hidden for guests', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());

    get(route('frontend.clips.show', $this->clip))->assertDontSee('Comments');
});

it('clip public page has a feeds button', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());

    get(route('frontend.clips.show', $this->clip))->assertSee('Feeds');
});

it('clip public page should display tags if a clip has any', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    get(route('frontend.clips.show', $this->clip))->assertDontSee('Tags');

    $this->clip->addTags(collect(['single tag', 'tides', 'testTags']));
    get(route('frontend.clips.show', $this->clip))->assertSee('Tags', 'testTags');
});

it('clip public page should display a series title and link if clip belongs to a series', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $series = SeriesFactory::create();

    get(route('frontend.clips.show', $this->clip))->assertDontSee($series->title);

    $this->clip->series_id = $series->id;
    $this->clip->save();
    get(route('frontend.clips.show', $this->clip))->assertSee($series->title);

    $this->mockHandler->append(new Response, new Response);
    get(route('frontend.clips.show', $this->clip))->assertSee(route('frontend.series.show', $series));
});

it('clip public page should display all alternative videos if a video has assets', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());

    $this->clip->addAsset(Asset::factory()->create([
        'original_file_name' => 'presentation.smil',
        'type' => Content::SMIL,
    ]));
    $this->clip->addAsset(Asset::factory()->create([
        'original_file_name' => 'composite.smil',
        'type' => Content::SMIL,
    ]));
    get(route('frontend.clips.show', $this->clip))
        ->assertSee(__('clip.frontend.show.presenter video stream'))
        ->assertSee(__('clip.frontend.show.presentation video stream'))
        ->assertSee(__('clip.frontend.show.composite video stream'));
});

it('clip public page should not display alternative videos if user is not allowed to view the video', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $this->clip->addAcls(collect([Acl::PORTAL()]));

    $this->clip->addAsset(Asset::factory()->create([
        'original_file_name' => 'presentation.smil',
        'type' => Content::SMIL,
    ]));
    $this->clip->addAsset(Asset::factory()->create([
        'original_file_name' => 'composite.smil',
        'type' => Content::SMIL,
    ]));

    get(route('frontend.clips.show', $this->clip))
        ->assertDontSee('presenter video stream')
        ->assertDontSee('presentation video stream')
        ->assertDontSee('composite video stream');
});

it('clip public page should display clip presenters if any', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $presenters = Presenter::factory(2)->create();

    get(route('frontend.clips.show', $this->clip))->assertDontSee('Tags');

    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $this->clip->addPresenters(collect($presenters->pluck('id')));
    get(route('frontend.clips.show', $this->clip))
        ->assertSee(Presenter::find(1)->getFullNameAttribute(), Presenter::find(2)->getFullNameAttribute());
});

it('clip public page should have navigate to previous and next clips if a clip belongs to a series', function () {
    SeriesFactory::withClips(3)->withAssets(2)->create();
    $clip = Clip::find(3);
    $previousClip = Clip::find(2);
    $nextClip = Clip::find(4);
    $this->mockHandler->append(
        $this->mockCheckApiConnection(),
        $this->mockVodSecureUrls()
    );

    get(route('frontend.clips.show', $clip))
        ->assertSee(__('common.previous'))
        ->assertSee(__('common.next'))
        ->assertSee(route('frontend.clips.show', $previousClip))
        ->assertSee(route('frontend.clips.show', $nextClip));
});

it('a signed in user can access frontend clip page if clip has a portal access', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $this->clip->addAcls(collect([Acl::PORTAL()]));

    get(route('frontend.clips.show', $this->clip))->assertDontSee('video id="target"', false);

    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    signInRole(Role::STUDENT);

    get(route('frontend.clips.show', $this->clip))->assertSee('mediaPlayer id="target"', false);
});

it('shows a login url and acl info if a clip has portal, password, or lms acl ', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    $this->clip->addAcls(collect([Acl::PORTAL()]));

    get(route('frontend.clips.show', $this->clip))
        ->assertSee(__('clip.frontend.this clip is exclusively accessible to logged-in users'));

    $this->clip->addAcls(collect([Acl::LMS()]));
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());

    get(route('frontend.clips.show', $this->clip))
        ->assertSee(__('clip.frontend.access to this clip is restricted to LMS course participants'));

    $this->clip->addAcls(collect([Acl::PASSWORD()]));
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());

    get(route('frontend.clips.show', $this->clip))
        ->assertSee(__('clip.frontend.this clip requires a password for access'));
});

it('redirects to clip page after user login, if a clip has a portal acl', function () {
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    // Simulate logging in
    $user = User::factory()->create(); // Ensure you have a User factory set up
    $user->assignRole(Role::MEMBER);
    $protectedPageUrl = route('frontend.clips.show', $this->clip);
    get($protectedPageUrl)->assertSessionHas(['url.intended']);
    $this->mockHandler->append($this->mockCheckApiConnection(), $this->mockVodSecureUrls());
    post(route('login'), [
        'username' => $user->username,
        'password' => 'password', // The password used when creating the user
    ])->assertRedirect($protectedPageUrl);
});

it('displays a ads text if the portal setting is set', function () {
    $setting = Setting::portal();
    $portalSetting = $setting->data;
    $portalSetting['player_show_article_link_in_player'] = true;
    $portalSetting['player_article_link_text'] = 'This is an ad text';
    $setting->data = $portalSetting;
    $setting->save();

    expect(Setting::portal()->data['player_show_article_link_in_player'])->toBeTrue();
    get(route('frontend.clips.show', $this->clip))->assertSee($portalSetting['player_article_link_text']);
});
