<?php

use App\Models\Clip;
use App\Models\Livestream;
use App\Services\OpencastService;
use Carbon\Carbon;
use Tests\Setup\WorksWithOpencastClient;

use function Pest\Laravel\artisan;
use function Pest\Laravel\travelTo;

uses()->group('backend');
uses(WorksWithOpencastClient::class);

beforeEach(function () {
    $this->mockHandler = $this->swapOpencastClient();
    $this->opencastService = app(OpencastService::class);
});

it('outputs a message and skip checks if Opencast server is not available', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());

    artisan('app:check-livestreams')->expectsOutputToContain('No Opencast server found or server is offline!');
});

it('outputs a message and skip checks if no active livestream rooms found', function () {
    $this->mockHandler->append($this->mockHealthResponse(), $this->mockNoResultsResponse());
    Livestream::factory()->create([
        'name' => 'Livestream Room 1',
        'clip_id' => null,
    ]);

    artisan('app:check-livestreams')->expectsOutputToContain('No active livestreams found');
});

it('it outputs a message if a livestream exists and it is still active', function () {
    $this->mockHandler->append($this->mockHealthResponse(), $this->mockNoResultsResponse());
    $clip = Clip::factory()->create();
    $livestream = Livestream::factory()->create([
        'name' => 'Livestream Room 1',
        'clip_id' => $clip->id,
        'time_availability_start' => Carbon::now()->subMinutes(10),
        'time_availability_end' => Carbon::now()->addHour(),
        'active' => true,
    ]);

    artisan('app:check-livestreams')->expectsOutputToContain("Livestream {$livestream->name} is still active.");
    $livestream->refresh();

    expect($livestream->clip_id)->toBe($clip->id);
});

it('disables a livestream if the end availability timestamp is equal or less than the current timestamp', function () {

    $this->mockHandler->append($this->mockHealthResponse(), $this->mockNoResultsResponse());
    $clip = Clip::factory()->create();
    $livestream = Livestream::factory()->create([
        'name' => 'Livestream Room 1',
        'clip_id' => $clip->id,
        'time_availability_start' => Carbon::now()->subMinutes(10),
        'time_availability_end' => Carbon::now()->addHour(),
        'active' => true,
    ]);

    travelTo(now()->addHours(2), function () use ($livestream) {
        artisan('app:check-livestreams')->expectsOutputToContain("Disable livestream {$livestream->name}.");
        $livestream->refresh();

        expect($livestream->clip_id)->toBeNull();
        expect($livestream->active)->toBe(0);
    });

});
