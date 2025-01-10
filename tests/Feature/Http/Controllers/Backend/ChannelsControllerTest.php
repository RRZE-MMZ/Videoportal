<?php

use App\Enums\Role;
use App\Models\Channel;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->moderatorWithChannel = User::factory()->create();
    $this->moderatorWithChannel->assignRole(Role::MODERATOR);
    $this->moderatorChannel = Channel::create([
        'url_handle' => '@'.Str::before($this->moderatorWithChannel->email, '@'),
        'name' => $this->moderatorWithChannel->getFullNameAttribute(),
        'description' => 'this is a test channel',
        'owner_id' => $this->moderatorWithChannel->id,
    ]);
});

uses()->group('backend');

it('denies access to simple user to create a channel', function () {
    get(route('channels.index'))->assertRedirect(route('login'));
    signInRole(Role::USER);
    get(route('channels.index'))->assertForbidden();
});

it('lists all channels to portal admins in index page', function () {
    $this->signInRole(Role::ADMIN);
    $channel = Channel::factory()->create();
    get(route('channels.index'))->assertSee(route('channels.edit', $channel));
});

it('lists only moderator channels in index page for a moderator', function () {
    $channel = Channel::factory()->create();
    signIn($this->moderatorWithChannel);

    get(route('channels.index'))
        ->assertSee(route('channels.edit', $this->moderatorChannel))
        ->assertDontSee(route('channels.edit', $channel));
});

it('validates the input to activate a channel', function () {
    $this->signInRole(Role::ADMIN);

    post(route('channels.store', [
        'url_handle' => '',
        'name' => fake()->sentence(256),
        'description' => fake()->sentence(1001),
    ]))->assertSessionHasErrors(['url_handle', 'name', 'description']);
});

it('allows access to moderators to create a channel', function () {
    signInRole(Role::MODERATOR);

    get(route('channels.index'))->assertOk()
        ->assertViewIs('backend.channels.index')
        ->assertViewHas(['channels']);
});

it('shows a channel introduction text if channel is not activated yet', function () {
    signInRole(Role::MODERATOR);
    get(route('channels.index'))
        ->assertSee(__('channel.backend.channel activation info text'));
});

it('asks to enable channels if moderator has no active channel', function () {
    signInRole(Role::MODERATOR);
    get(route('channels.index'))->assertSee(__('common.actions.activate'));
});

it('it validates url handle and channel title the form for channel activation', function () {
    signInRole(Role::MODERATOR);

    $attributes = [];
    post(route('channels.store'), $attributes)
        ->assertSessionHasErrors(['url_handle', 'name']);
});

it('creates a new channel when user is activates his channel', function () {
    $user = signInRole(Role::MODERATOR);
    $attributes = [
        'url_handle' => '@'.Str::before(auth()->user()->email, '@'),
        'name' => $user->getFullNameAttribute(),
        'description' => 'this is a test channel',
    ];
    post(route('channels.store'), $attributes)
        ->assertRedirectToRoute('channels.index');

    assertDatabaseHas('channels', [
        'url_handle' => $attributes['url_handle'],
    ]);
});

it('index page shows a list of channels if user has channels activated', function () {
    $user = signInRole(Role::MODERATOR);
    $attributes = [
        'url_handle' => '@'.Str::before(auth()->user()->email, '@'),
        'name' => $user->getFullNameAttribute(),
        'description' => 'this is a test channel',
    ];
    post(route('channels.store'), $attributes);

    $channelInfo = $user->channels()->first();
    get(route('channels.index'))->assertSee($channelInfo->name);
});

test('a users is not allowed activate another users channel', function () {
    $userA = signInRole(Role::MODERATOR);
    auth()->logout();
    signInRole(Role::MODERATOR);
    $attributes = [
        'url_handle' => '@'.Str::before($userA->email, '@'),
        'name' => $userA->getFullNameAttribute(),
        'description' => 'this is a test channel',
    ];

    post(route('channels.store'), $attributes)->assertForbidden();
    assertDatabaseMissing('channels', [
        'url_handle' => $attributes['url_handle'],
    ]);
});

it('has an edit channel page', function () {
    signIn($this->moderatorWithChannel);

    get(route('channels.edit', $this->moderatorChannel))
        ->assertViewIs('backend.channels.edit')
        ->assertSee('name')
        ->assertSee('description')
        ->assertOk();
});

it('has validation for channel name and description when updating a channel', function () {
    signIn($this->moderatorWithChannel);
    $attributes = [
        'name' => '',
        'description' => fake()->words(1500),
    ];
    patch(route('channels.update', $this->moderatorChannel), $attributes)
        ->assertSessionHasErrors('name')
        ->assertSessionHasErrors('description');
});

it('moderator can update channel basic infos like name and description', function () {
    signIn($this->moderatorWithChannel);
    $attributes = [
        'name' => 'another name',
        'description' => 'another description',
    ];
    patch(route('channels.update', $this->moderatorChannel), $attributes)
        ->assertRedirectToRoute('channels.edit', $this->moderatorChannel);

    $this->moderatorChannel->refresh();

    expect($this->moderatorChannel->name)->toBe($attributes['name']);
    expect($this->moderatorChannel->description)->toBe($attributes['description']);
});

it('has a button for uploading a new banner image using filepond', function () {
    signIn($this->moderatorWithChannel);
    get(route('channels.edit', $this->moderatorChannel))
        ->assertSee(__('channel.backend.actions.upload channel banner image'))
        ->assertSee('filepond');
});
