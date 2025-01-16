<?php

use App\Models\Livestream;
use App\Models\User;
use App\Services\OpencastService;
use Facades\Tests\Setup\SeriesFactory;
use Illuminate\Support\Carbon;
use Tests\Setup\WorksWithOpencastClient;

use function Pest\Laravel\artisan;

uses()->group('backend');
uses(WorksWithOpencastClient::class);

beforeEach(function () {
    $this->mockHandler = $this->swapOpencastClient();
    $this->opencastService = app(OpencastService::class);
    User::factory()->create(['email' => 'admin@test.com']);
});

it('outputs a message and skip checks if Opencast server is not available', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());

    artisan('app:enable-livestreams')->expectsOutputToContain('No Opencast server found or server is offline!');
});

it('outputs a message and skip checks if no Opencast scheduled events found for the next 10 minutes', function () {
    $this->mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockNoResultsResponse(),
        $this->mockNoResultsResponse()
    );

    artisan('app:enable-livestreams')->expectsOutputToContain('No Opencast scheduled events found for the next 10 minutes');
});

it('outputs a message if Opencast scheduled events found for the next 10 minutes', function () {
    $series = SeriesFactory::withClips(3)->withOpencastID()->create();
    Livestream::factory()->create();
    $livestreamClip = $series->clips()->first();
    $livestreamClip->is_livestream = true;
    $livestreamClip->save();

    $this->mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockNoResultsResponse(),
        $this->mockScheduledEvents($series, 1, Carbon::now()->addMinutes(5), Carbon::now()->addMinutes(26))
    );

    artisan('app:enable-livestreams')
        ->expectsOutputToContain("Series '{$series->title}' has a livestream clip now try to enable".
            ' wowza app test-lecture-hall for this clip');
});

it('notifies the admins if a livestream room is enables via cronjob', function () {
    $series = SeriesFactory::withClips(3)->withOpencastID()->create();
    Livestream::factory()->create();
    $livestreamClip = $series->clips()->first();
    $livestreamClip->is_livestream = true;
    $livestreamClip->save();

    $this->mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockNoResultsResponse(),
        $this->mockScheduledEvents($series, 1, Carbon::now()->addMinutes(5), Carbon::now()->addMinutes(26))
    );

    artisan('app:enable-livestreams')
        ->expectsOutputToContain("Series '{$series->title}' has a livestream clip now try to enable".
            ' wowza app test-lecture-hall for this clip');
});

it('updates clip metadata and set the livestream start and end times', function () {
    $series = SeriesFactory::withClips(3)->withOpencastID()->create();
    $livestreamRoom = Livestream::factory()->create();
    $livestreamClip = $series->clips()->first();
    $livestreamClip->is_livestream = true;
    $livestreamClip->save();
    $endTime = Carbon::now()->addMinutes(26);

    $this->mockHandler->append(
        $this->mockHealthResponse(),
        $this->mockNoResultsResponse(),
        $this->mockScheduledEvents($series, 1, Carbon::now()->addMinutes(6), $endTime)
    );

    artisan('app:enable-livestreams');

    expect($livestreamRoom->refresh()->clip_id)
        ->toBe($livestreamClip->id);
    expect($livestreamRoom->active)->toBe(1);
});
