<?php

use App\Enums\Role;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Facades\Tests\Setup\PodcastFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses()->group('backend');

beforeEach(function () {
    $this->podcast = PodcastFactory::ownedBy(signInRole(Role::MODERATOR))->create();
});

it('a visitor is not allowed to view manage podcast pages', function () {
    auth()->logout();
    get(route('podcasts.edit', $this->podcast))->assertRedirectToRoute('login');
    get(route('podcasts.create'))->assertRedirectToRoute('login');
    post(route('podcasts.store'), $this->podcast->toArray())->assertRedirectToRoute('login');
    put(route('podcasts.update', $this->podcast))->assertRedirectToRoute('login');
    delete(route('podcasts.destroy', $this->podcast))->assertRedirectToRoute('login');
});

it('denies access to edit a podcasts to an authenticated student', function () {
    auth()->logout();
    signInRole(Role::STUDENT);

    get(route('podcasts.edit', $this->podcast))->assertForbidden();
    get(route('podcasts.create'))->assertForbidden();
    post(route('podcasts.store'), $this->podcast->toArray())->assertForbidden();
    put(route('podcasts.update', $this->podcast))->assertForbidden();
    delete(route('podcasts.destroy', $this->podcast))->assertForbidden();
});

it('shows only users podcast shows if user is a moderator', function () {
    auth()->logout();
    $podcast = Podcast::factory()->create();

    signInRole(Role::MODERATOR);

    get(route('podcasts.index'))->assertDontSee($podcast->title);

});

it('shows all podcasts to portal admins', function () {
    auth()->logout();
    $podcast = Podcast::factory()->create();
    signInRole(Role::ASSISTANT);

    get(route('podcasts.index'))->assertSee($podcast->title);
});

it('denies access to edit page to a moderator without access rights', function () {
    auth()->logout();

    //sign in another user
    signInRole(Role::MODERATOR);

    get(route('podcasts.edit', $this->podcast))->assertForbidden();
});

it('shows podcast create page a moderator user with all podcast page fields', function () {
    get(route('podcasts.create'))
        ->assertOk()
        ->assertSee(__('common.forms.title'))
        ->assertSee(__('common.forms.description'))
        ->assertSee('Host(s)')
        ->assertSee('Guest(s)')
        ->assertViewIs('backend.podcasts.create');
});

it('stores a new podcast in the database', function () {
    $newPodcast = Podcast::factory()->raw([
        'owner_id' => auth()->user()->id,
        'image' => null,
    ]);
    expect(Podcast::all()->count())->toBe(1);

    post(route('podcasts.store'), $newPodcast);
    expect(Podcast::all()->count())->toBe(2);
    assertDatabaseHas('podcasts', ['title' => $newPodcast['title']]);
});

it('show podcasts edit page for podcasts owner', function () {
    get(route('podcasts.edit', $this->podcast))
        ->assertOk()
        ->assertViewIs('backend.podcasts.edit');
});

it('denies updating a podcast to a non privileged moderator', function () {
    auth()->logout();

    //sign in another user
    signInRole(Role::MODERATOR);

    patch(route('podcasts.update', $this->podcast), ['title' => 'title_changed'])->assertForbidden();
});

it('lists all podcast episodes in podcast edit page', function () {
    $this->podcast->episodes()->save(PodcastEpisode::factory()->create());
    $podcastEpisode = $this->podcast->episodes()->first();

    get(route('podcasts.edit', $this->podcast))->assertSee($podcastEpisode->title);
});

it('denies a non authorized user to delete a podcast', function () {
    auth()->logout();

    signInRole(Role::MODERATOR);

    delete(route('podcasts.destroy', $this->podcast))->assertForbidden();
});

it('allows deleting a podcast to podcast owner', function () {
    delete(route('podcasts.destroy', $this->podcast))->assertRedirectToRoute('podcasts.index');

    assertDatabaseMissing('podcasts', ['id' => $this->podcast->id]);
});

test('deleting a podcast will also delete all it\'s episodes', function () {
    $episode = PodcastEpisode::factory()->create();
    $this->podcast->episodes()->save($episode);
    delete(route('podcasts.destroy', $this->podcast));

    assertDatabaseMissing('podcasts', ['id' => $this->podcast->id]);
    assertDatabaseMissing('podcast_episodes', ['id' => $episode->id, 'podcast_id' => $this->podcast->id]);
});
