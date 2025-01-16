<?php

use App\Enums\OpencastWorkflowState;
use App\Models\Series;
use App\Services\OpencastService;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\FileFactory;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\Setup\WorksWithOpencastClient;

uses(RefreshDatabase::class);

uses(WithFaker::class);

uses(WorksWithOpencastClient::class);

beforeEach(function () {
    $this->mockHandler = $this->swapOpencastClient();

    $this->opencastService = app(OpencastService::class);
});

it('searches for clips with no assets', function () {
    SeriesFactory::withClips(1)->withAssets(2)->withOpencastID()->create();

    $this->artisan('opencast:finished-events')
        ->expectsOutputToContain('No empty clips found');
});

it('searches for clips with series', function () {
    ClipFactory::create();

    $this->artisan('opencast:finished-events')
        ->expectsOutputToContain('No empty clips found');
});

it('searched for clips with opencast series id', function () {
    SeriesFactory::withClips(1)->create();
    expect(Series::hasOpencastSeriesID()->count())->toEqual(0);

    $this->artisan('opencast:finished-events')
        ->expectsOutputToContain('No empty clips found');
});

it('fetches opencast events for a given series', function () {
    Storage::fake('videos');
    $fakeStorage = Storage::fake('opencast_archive');

    $archiveVersion = 2;
    $audioUID = $this->faker->uuid();
    $videoHD_UID = $this->faker->uuid();
    $opencastEventID = $this->faker->uuid();

    $seriesWithoutAssets = SeriesFactory::withClips(1)->withOpencastID()->create();
    $clip = $seriesWithoutAssets->clips()->first();
    $clip->opencast_event_id = $opencastEventID;
    $clip->save();

    $this->mockHandler->append(
        $this->mockEventAssets($videoHD_UID, $audioUID),
        $this->mockEventResponse($seriesWithoutAssets, OpencastWorkflowState::SUCCEEDED, 4, $opencastEventID),
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

    $this->artisan('opencast:finished-events')
        ->expectsOutputToContain("Videos from Clip {$clip->title} is online");
});

it('output a console message do nothing if no opencast event id found for an empty clip', function () {
    Storage::fake('videos');
    $audioUID = $this->faker->uuid();
    $videoHD_UID = $this->faker->uuid();

    $seriesWithoutAssets = SeriesFactory::withClips(1)->withOpencastID()->create();
    $clip = $seriesWithoutAssets->clips()->first();

    $this->mockHandler->append(
        $this->mockEventAssets($videoHD_UID, $audioUID),
        $this->mockEventResponse($seriesWithoutAssets, OpencastWorkflowState::SUCCEEDED),
        $this->mockEventAssets($videoHD_UID, $audioUID),
        $this->mockEventResponse($seriesWithoutAssets, OpencastWorkflowState::SUCCEEDED),
    );

    $this->artisan('opencast:finished-events')
        ->expectsOutputToContain("No Opencast Event found for Clip {$clip->title} | [ID]:{$clip->id}");
});
