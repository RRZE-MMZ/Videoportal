<?php

use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

uses()->group('unit');

beforeEach(function () {
    // create the podcast to link the episode with the id = 1 otherwise tests are going to fail
    $this->podcast = Podcast::factory()->create();
    $this->podcastEpisode = PodcastEpisode::factory()->create();
});

test('to array', function () {
    expect(array_keys($this->podcastEpisode->toArray()))->toBe([
        'id', 'episode_number', 'recording_date', 'title', 'slug', 'podcast_id', 'description', 'notes',
        'transcription', 'image_id', 'is_published', 'website_url', 'spotify_url', 'apple_podcasts_url',
        'old_episode_id', 'published_at', 'folder_id', 'owner_id', 'created_at', 'updated_at', 'owner', 'podcast',
    ]);
});

it('belongs to an owner', function () {
    expect($this->podcastEpisode->owner())->toBeInstanceOf(BelongsTo::class);
});

it('belongs to a podcasts', function () {
    expect($this->podcastEpisode->podcast())->toBeInstanceOf(BelongsTo::class);
});

it('belongs to an image with the attribute of podcasts cover', function () {
    expect($this->podcastEpisode->cover())->toBeInstanceOf(BelongsTo::class);
});

it('has many comments', function () {
    expect($this->podcastEpisode->comments())->toBeInstanceOf(MorphMany::class);
});
