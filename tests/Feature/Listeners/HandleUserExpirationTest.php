<?php

use App\Events\UserExpired;
use App\Listeners\HandleUserExpiration;
use App\Models\Activity;
use App\Models\Clip;
use App\Models\Comment;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Models\Series;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('it listens for UserExpired Event', function () {
    Event::fake();

    Event::assertListening(
        UserExpired::class,
        HandleUserExpiration::class
    );
});

it('should delete user subscriptions', function () {
    $user = User::factory()->create();
    $user->subscriptions()->attach(Series::factory(3)->create());
    expect($user->subscriptions()->count())->toBe(3);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($user->subscriptions()->count())->toBe(0);
});

it('resets series ownership for user series if user is expired', function () {
    $user = User::factory()->create();
    $seriesA = Series::factory()->create(['owner_id' => $user->id]);
    $seriesB = Series::factory()->create(['owner_id' => $user->id]);
    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($seriesA->refresh()->owner)->toBeNull();
    expect($seriesB->refresh()->owner)->toBeNull();

});

it('resets clips ownership for user clips if user is expired', function () {
    $user = User::factory()->create();
    $clipA = Clip::factory()->create(['owner_id' => $user->id]);
    $clipB = Clip::factory()->create(['owner_id' => $user->id]);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($clipA->refresh()->owner)->toBeNull();
    expect($clipB->refresh()->owner)->toBeNull();
});

it('deletes user comments if user is expired', function () {
    $user = User::factory()->create();
    Comment::factory(2)->create([
        'owner_id' => $user->id,
        'content' => 'test comment',
        'type' => 'frontend',
    ]);

    expect($user->comments()->count())->toBe(2);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($user->comments()->count())->toBe(0);
});

it('resets podcasts ownership for user clips if user is expired', function () {
    $user = User::factory()->create();
    $podcastA = Podcast::factory()->create(['owner_id' => $user->id]);
    $podcastB = Podcast::factory()->create(['owner_id' => $user->id]);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($podcastA->refresh()->owner)->toBeNull();
    expect($podcastB->refresh()->owner)->toBeNull();
});

it('resets podcast episodes ownership for user clips if user is expired', function () {
    $user = User::factory()->create();
    $podcastEpisodeA = PodcastEpisode::factory()->create(['owner_id' => $user->id]);
    $podcastEpisodeB = PodcastEpisode::factory()->create(['owner_id' => $user->id]);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($podcastEpisodeA->refresh()->owner)->toBeNull();
    expect($podcastEpisodeB->refresh()->owner)->toBeNull();
});

it('resets supervised clips ownership for user clips if user is expired', function () {
    $user = User::factory()->create();
    $clipA = Clip::factory()->create(['supervisor_id' => $user->id]);
    $clipB = Clip::factory()->create(['supervisor_id' => $user->id]);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    expect($clipA->refresh()->supervisor)->toBeNull();
    expect($clipB->refresh()->supervisor)->toBeNull();
});

it('anonymize user activities if user is expired', function () {
    $user = User::factory()->create();
    Activity::factory()->create([
        'user_id' => $user->id,
        'content_type' => 'series',
        'object_id' => 1,
        'change_message' => 'Created series',
        'user_real_name' => $user->getFullNameAttribute(),
        'action_flag' => 1,
        'changes' => [],
    ]);
    Activity::factory()->create([
        'user_id' => $user->id,
        'content_type' => 'series',
        'object_id' => 1,
        'change_message' => 'Updated series',
        'user_real_name' => $user->getFullNameAttribute(),
        'action_flag' => 1,
        'changes' => [],
    ]);

    Event::fake();
    (new HandleUserExpiration)->handle(new UserExpired($user));

    assertDatabaseHas('activities', [
        'user_id' => $user->id,
        'content_type' => 'series',
        'object_id' => 1,
        'change_message' => 'Created series',
        'user_real_name' => 'DELETED USER',
    ]);
});
