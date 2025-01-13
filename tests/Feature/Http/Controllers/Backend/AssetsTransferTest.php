<?php

use App\Enums\Content;
use App\Enums\OpencastWorkflowState;
use App\Enums\Role;
use App\Jobs\CreateWowzaSmilFile;
use App\Jobs\TransferAssetsJob;
use App\Mail\AssetsTransferred;
use App\Services\OpencastService;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\FileFactory;
use Facades\Tests\Setup\PodcastFactory;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Tests\Setup\WorksWithOpencastClient;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withoutExceptionHandling;

uses()->beforeEach(function () {
    Storage::fake('videos');
    Storage::fake('local');
    Storage::fake('thumbnails');
    $audioFile = FileFactory::audioFile();
    $randomString = Str::random(10); // Use Laravel's Str helper
    $this->filePath = "/tmp/{$randomString}/Sample_Audio_file.mp3";
    // create two test images in the disks
    Storage::disk('local')->put($this->filePath, $audioFile->getContent());
});

uses(WorksWithOpencastClient::class);

test('a moderator can upload a video file', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()])
        ->assertRedirect(route('clips.edit', $clip));
    $asset = $clip->assets()->first();
    assertDatabaseHas('assets', ['path' => $asset->path]);
    Storage::disk('videos')->assertExists($asset->path);
});

test('uploading a video file will delete tmp file from disk', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()])
        ->assertRedirect(route('clips.edit', $clip));
    $asset = $clip->assets()->first();

    assertDatabaseHas('assets', ['path' => $asset->path]);
    Storage::disk('videos')->assertExists($asset->path);
    Storage::disk('local')->assertMissing($asset->original_file_name);
});

test('an asset must be a video file', function () {
    post(route(
        'admin.clips.asset.transferSingle',
        ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create()
    ), [
        'asset' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertSessionHasErrors('asset');
});

test('uploading an asset should save asset duration', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);

    expect(FFMpeg::fromDisk('videos')->open($clip->assets()->first()->path)->getDurationInSeconds())->toBe(10);
});

test('uploading an asset should create a clip poster', function () {
    Storage::fake('thumbnails');
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    post(route('admin.clips.asset.transferSingle', $clip), ['asset' => $file = FileFactory::videoFile()]);
    $clip->refresh();

    Storage::disk('thumbnails')->assertExists($clip->posterImage);
});

test('a moderator cannot see copy assets from dropzone button', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    get(route('clips.edit', $clip))->assertDontSee(__('clip.backend.actions.transfer files from drop zone'));
});

it('has a transfer view for dropzone files', function () {
    get(route(
        'admin.clips.dropzone.listFiles',
        ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create()
    ))->assertOk()->assertViewIs('backend.clips.dropzone.listFiles');
});

it('denies access to view dropzone files for a not owned clip', function () {
    signInRole(Role::MODERATOR);
    get(route('admin.clips.dropzone.listFiles', ClipFactory::create()))->assertForbidden();
});

test('a dropzone transfer view should list all files with sha1 hash', function () {
    $disk = Storage::fake('video_dropzone');
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    get(route('admin.clips.dropzone.listFiles', ['clip' => $clip]))
        ->assertOk()
        ->assertSee('no videos');
    $disk->putFileAs('', FileFactory::videoFile(), 'export_video_1080.mp4');

    get(route('admin.clips.dropzone.listFiles', ['clip' => $clip]))
        ->assertSee(sha1('export_video_1080.mp4'));
});

test('a moderator cannot transfer dropzone files for a not owned clip', function () {
    signInRole(Role::MODERATOR);
    post(route('admin.clips.dropzone.transfer', ClipFactory::create()))->assertForbidden();
});

it('transfer files from dropzone to clip', function () {
    withoutExceptionHandling();
    $fakeStorage = Storage::fake('video_dropzone');
    $fakeStorage->putFileAs('', FileFactory::videoFile(), 'export_video_1080.mp4');
    $fakeStorage->putFileAs('', FileFactory::videoFile(), 'export_video_720.mp4');
    $fakeStorage->putFileAs('', FileFactory::audioFile(), 'export_audio.mp3');
    $fakeStorage->putFileAs('', FileFactory::videoFile(), 'export_video_360.mp4');
    $files = fetchDropZoneFiles()->sortBy('date_modified');
    $videoHashHD = $files->keys()->first();
    $videoHashSD = $files->keys()->last();
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();

    followingRedirects()->post(
        route('admin.clips.dropzone.transfer', $clip),
        ['files' => [$videoHashHD, $videoHashSD]]
    )->assertOk();

    get(route('clips.edit', $clip))
        ->assertSee($files->first()['name'])
        ->assertSee($files->last()['name']);

    expect($clip->getAssetsByType(Content::PRESENTER)->first()->type)->toBe(Content::PRESENTER());
    expect($clip->getAssetsByType(Content::SMIL)->first()->type)->toBe(Content::SMIL());
});

it('should queue the transfer dropzone to clip job', function () {
    Bus::fake();
    post(route(
        'admin.clips.dropzone.transfer',
        ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create()
    ), [
        'files' => [
            sha1('test_file_name'),
        ],
    ]);

    Bus::assertChained([
        TransferAssetsJob::class,
        CreateWowzaSmilFile::class,
    ]);
});

it('should send an email after transfer job is completed', function () {
    Mail::fake();
    post(route(
        'admin.clips.dropzone.transfer',
        ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create()
    ), [
        'files' => [
            sha1('test_file_name'),
        ],
    ]);

    Mail::assertQueued(AssetsTransferred::class);
});

it('should show an empty list if no opencast events found', function () {
    $series = SeriesFactory::withClips(2)
        ->ownedBy(signInRole(Role::MODERATOR))
        ->withOpencastID()
        ->create();

    get(route('admin.clips.opencast.listEvents', ['clip' => $series->clips()->first()]))
        ->assertOk()
        ->assertSee(__('opencast.backend.no processed events found for this series'));
});

test('opencast transfer view should list all events with event uid', function () {
    $series = SeriesFactory::withClips(2)
        ->ownedBy(signInRole(Role::MODERATOR))
        ->withOpencastID()
        ->create();
    $mockHandler = $this->swapOpencastClient();
    app(OpencastService::class);
    $mockHandler->append(
        $this->mockEventResponse($series, OpencastWorkflowState::SUCCEEDED),
        $this->mockEventResponse($series, OpencastWorkflowState::PAUSED)
    );
    get(route('admin.clips.opencast.listEvents', ['clip' => $series->clips()->first()]))
        ->assertOk()
        ->assertViewHas('events', function (Collection $collection) {
            return $collection->count() == 2;
        });
});

test('a moderator cannot view opencast events list for a not owned clip', function () {
    signInRole(Role::MODERATOR);
    get(route('admin.clips.opencast.listEvents', ClipFactory::create()))->assertForbidden();
});

it('should queue the transfer opencast assets job', function () {
    Bus::fake();
    $opencastEventID = $this->faker->uuid();
    $archiveVersion = 2;
    $audioUID = $this->faker->uuid();
    $videoHD_UID = $this->faker->uuid();
    $mockHandler = $this->swapOpencastClient();
    app(OpencastService::class);
    $mockHandler->append(
        $this->mockEventAssets($videoHD_UID, $audioUID),
        $this->mockEventByEventID($opencastEventID, OpencastWorkflowState::SUCCEEDED, $archiveVersion),
    );
    app(OpencastService::class);

    post(route(
        'admin.clips.opencast.transfer',
        ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create()
    ), [
        'eventID' => $this->faker->uuid(),
    ]);
    Bus::assertChained([
        TransferAssetsJob::class,
        CreateWowzaSmilFile::class,
    ]);
});

it('shows an error on opencast transfer if event is not a uuid', function () {
    $clip = ClipFactory::ownedBy(signInRole(Role::MODERATOR))->create();
    post(route('admin.clips.opencast.transfer', $clip), ['eventID' => 'test'])
        ->assertSessionHasErrors('eventID');
    $eventID = $this->faker->uuid();

    post(route('admin.clips.opencast.transfer', $clip), ['eventID' => $eventID])
        ->assertSessionDoesntHaveErrors('eventID');
});

it('transfers opencast event assets to clip', function () {
    $fakeStorage = Storage::fake('opencast_archive');

    $opencastEventID = $this->faker->uuid();
    $archiveVersion = 2;
    $audioUID = $this->faker->uuid();
    $videoHD_UID = $this->faker->uuid();

    $mockHandler = $this->swapOpencastClient();
    app(OpencastService::class);
    $mockHandler->append(
        $this->mockEventAssets($videoHD_UID, $audioUID),
        $this->mockEventByEventID($opencastEventID, OpencastWorkflowState::SUCCEEDED, $archiveVersion),
    );

    $fakeStorage
        ->putFileAs(
            '',
            FileFactory::videoFile(),
            "/archive/mh_default_org/{$opencastEventID}/{$archiveVersion}/{$audioUID}.mp3"
        );
    $fakeStorage
        ->putFileAs(
            '',
            FileFactory::videoFile(),
            "/archive/mh_default_org/{$opencastEventID}/{$archiveVersion}/{$videoHD_UID}.m4v"
        );

    $clip = ClipFactory::ownedBy(signInRole(Role::ADMIN))->create();
    post(
        route('admin.clips.opencast.transfer', $clip),
        ['eventID' => $opencastEventID]
    )->assertStatus(302);

    $mockHandler->append($this->mockHealthResponse());

    get(route('clips.edit', $clip))
        ->assertSee($videoHD_UID)
        ->assertSee($audioUID)
        ->assertSee('presenter.smil');
});

it('validates audio files to upload to a podcast', function () {
    $podcast = PodcastFactory::withEpisodes(1)->ownedBy(signInRole(Role::MODERATOR))->create();
    $episode = $podcast->episodes()->first();

    post(route('admin.podcasts.episode.transferPodcastAudioFile', compact('podcast', 'episode')), [])
        ->assertSessionHasErrors();
});

test('a moderator can upload an audio file to a podcast episode', function () {
    $podcast = PodcastFactory::withEpisodes(1)->ownedBy(signInRole(Role::MODERATOR))->create();
    $episode = $podcast->episodes()->first();

    post(route('admin.podcasts.episode.transferPodcastAudioFile', compact('podcast', 'episode')), [
        'asset' => $this->filePath,
    ])
        ->assertStatus(302);

    expect($episode->assets()->count())->toBe(1);
});
