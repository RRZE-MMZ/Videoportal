<?php

use App\Models\Image;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Facades\Tests\Setup\FileFactory;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

uses()->group('backend');

beforeEach(function () {
    $this->disk = Storage::fake('local');

    $this->podcast = Podcast::factory()
        ->create([
            'image_id' => Image::factory()->create(['file_name' => 'avatar.png']),
        ]);
    // Create dummy folders for testing
    Storage::makeDirectory('podcasts-files/courseID_'.$this->podcast->old_podcast_id);
    $this->disk->putFileAs(
        'podcasts-files/courseID_'.$this->podcast->old_podcast_id,
        FileFactory::imageFile(),
        'avatar.png'
    );

    // Create test podcasts in the database
    Podcast::factory(2)->create();
});

it('renames folders correctly when matching old  pattern', function () {
    $oldFolderPath = 'podcasts-files/courseID_'.$this->podcast->old_podcast_id;
    artisan('podcasts:rename-folders')
        ->expectsOutputToContain('Moving file from podcasts folder to images folder')
        ->assertExitCode(0);

    Storage::assertMissing($oldFolderPath.'/'.$this->podcast->cover->file_name);
    Storage::assertExists('images/'.$this->podcast->cover->file_name);
});

it('skips folders that do not match the pattern', function () {
    Storage::makeDirectory('podcasts-files/covers/invalid_folder');
    artisan('podcasts:rename-folders')
        ->doesntExpectOutput('Renamed podcasts-files/covers/invalid_folder to')
        ->assertExitCode(0);

    Storage::assertExists('podcasts-files/covers/invalid_folder');
});

it('moves and renames also episode files inside the podcasts directory', function () {
    $episode = PodcastEpisode::factory()->create([
        'image_id' => Image::factory()->create(['file_name' => 'avatar.png']),
        'podcast_id' => $this->podcast->id,
    ]);
    Storage::makeDirectory('podcasts-files/clipID_'.$episode->old_episode_id);
    $this->disk->putFileAs(
        'podcasts-files/clipID_'.$episode->old_episode_id,
        FileFactory::imageFile(),
        'avatar.png'
    );
    $oldFolderPath = 'podcasts-files/clipID_'.$episode->old_episode_id;

    artisan('podcasts:rename-folders')
        ->expectsOutputToContain('Moving file from podcasts episode folder to images folder')
        ->assertExitCode(0);

    Storage::assertMissing($oldFolderPath.'/'.$episode->cover->file_name);
    Storage::assertExists('images/'.$episode->cover->file_name);
});
