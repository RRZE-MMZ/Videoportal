<?php

use App\Enums\OpencastWorkflowState;
use App\Models\Clip;
use App\Models\User;
use App\Services\OpencastService;
use Facades\Tests\Setup\SeriesFactory;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Setup\WorksWithOpencastClient;

uses(WithFaker::class);
uses(WorksWithOpencastClient::class);
uses()->group('unit');

it('returns default values if guzzle response is empty', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    $results = $this->opencastService->getHealth();

    expect($results['status'])->toEqual('failed');
});

it('fetch opencast health status', function () {
    $this->mockHandler->append($this->mockHealthResponse());
    $results = $this->opencastService->getHealth();

    expect($results['description'])->toEqual('Opencast node\'s health status');
});

it('fetches opencast catalog info for a given series', function () {
    $series = SeriesFactory::withOpencastID()->create();

    $this->mockHandler->append(
        $this->mockSeriesMetadata($series), // seriesInfo
    );

    $seriesInfo = $this->opencastService->getSeries($series);
    expect($seriesInfo->isNotEmpty())->toBeTrue();
    expect($seriesInfo)->toHaveKey('identifier');
});

it('return an empty collection if server is not available for getting series info', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $response = $this->opencastService->getSeries($series);

    // Assert the result is an empty collection
    expect($response)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($response)->toBeEmpty();
});

it('fetches opencast series info  with all workflows for a given series', function () {
    $series = SeriesFactory::create();

    $this->mockHandler->append(
        $this->mockHealthResponse(), // health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), // recording
        $this->mockNoResultsResponse(), // running
        $this->mockNoResultsResponse(), // scheduled
        $this->mockNoResultsResponse(), // failed
        $this->mockNoTrimmingResultsResponse(), // trimming
        $this->mockNoResultsResponse(), // upcoming
    );

    $seriesInfo = $this->opencastService->getSeriesInfo($series);

    expect($seriesInfo->isNotEmpty())->toBeTrue();
    expect($seriesInfo)->toHaveKey('health');
    expect($seriesInfo)->toHaveKey('metadata');
    expect($seriesInfo)->toHaveKey(OpencastWorkflowState::RECORDING->lower());
    expect($seriesInfo)->toHaveKey(OpencastWorkflowState::RUNNING->lower());
    expect($seriesInfo)->toHaveKey(OpencastWorkflowState::SCHEDULED->lower());
    expect($seriesInfo)->toHaveKey(OpencastWorkflowState::FAILED->lower());
    expect($seriesInfo)->toHaveKey(OpencastWorkflowState::TRIMMING->lower());
    expect($seriesInfo)->toHaveKey('upcoming');
});

it('return an empty collection for getting events by status if server is not available', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );

    $response = $this->opencastService->getEventsByStatus(OpencastWorkflowState::RUNNING, $series);

    // Assert the result is an empty collection
    expect($response)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($response)->toBeEmpty();
});

it('logs the exception message as error if server is not available for getting events by status', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->opencastService->getEventsByStatus(OpencastWorkflowState::RUNNING, $series);

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('logs the exception message as error if server is not available for getting events by status and date', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->opencastService->getEventsByStatusAndByDate(
        OpencastWorkflowState::RUNNING,
        $series,
        Carbon::now(),
        Carbon::now()->addDays(4)
    );

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('fetch events by status and date', function () {
    $this->mockHandler->append(
        $this->mockEventResponse(SeriesFactory::withOpencastID()->create(), OpencastWorkflowState::SUCCEEDED)
    );

    $events = $this->opencastService->getEventsByStatusAndByDate(
        OpencastWorkflowState::RUNNING,
        null,
        Carbon::now(),
        Carbon::now()->addDays(4)
    );
    expect($events)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($events)->not->toBeEmpty();
});

it('fetch events for a series by status and date', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockEventResponse($series, OpencastWorkflowState::FAILED)
    );

    $events = $this->opencastService->getEventsByStatusAndByDate(
        OpencastWorkflowState::FAILED,
        $series,
        Carbon::now(),
        Carbon::now()->addDays(4)
    );
    expect($events)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($events)->not->toBeEmpty();
});

it('fetches an empty collection for opencast series info if no opencast server is available', function () {
    $series = SeriesFactory::create();

    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );

    $seriesInfo = $this->opencastService->getSeriesInfo($series);

    expect($seriesInfo->get('health')->get('status'))->toBe('failed');
});

it('creates an opencast series', function () {
    $this->mockHandler->append($this->mockCreateSeriesResponse());

    $response = $this->opencastService->createSeries(SeriesFactory::create());

    expect($response->getStatusCode())->toEqual(201);
});

it('throws an exception if create opencast series has no arguments', function () {
    $this->expectException(ArgumentCountError::class);
    $this->opencastService->createSeries();
});

test('create opencast series must have a portal series eloquent model', function () {
    $this->mockHandler->append($this->mockCreateAdminUserResponse());
    expect($this->opencastService->createSeries(SeriesFactory::create()))->toBeInstanceOf(Response::class);
});

it('creates an opencast admin user', function () {
    $this->mockHandler->append($this->mockCreateAdminUserResponse());

    $response = $this->opencastService->createUser(User::factory()->create());

    expect($response->getStatusCode())->toEqual(201);
});

it('throws an exception if create user has no arguments', function () {
    $this->expectException(ArgumentCountError::class);
    $this->opencastService->createUser();
});

it('throws an exception if create user argument is no eloquent model', function () {
    $this->expectException(TypeError::class);
    $this->opencastService->createUser('test');
});

it('logs the exception message as error if server is not available for creating a new user', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->opencastService->createUser(User::factory()->create());

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

test('create user must have an user eloquent model', function () {
    $this->mockHandler->append($this->mockCreateAdminUserResponse());
    expect($this->opencastService->createUser(User::factory()->create()))->toBeInstanceOf(Response::class);
});

it('does not create an opencast series if server is unavailable', function () {
    $this->mockHandler->append($this->mockServerNotAvailable());
    $series = SeriesFactory::create();
    $response = $this->opencastService->createSeries($series);

    expect($response->getStatusCode())->toEqual(200);
});

it('ingest a media package to opencast', function () {
    Storage::fake('videos');
    $this->mockHandler->append($this->mockIngestMediaPackageResponse());
    $series = SeriesFactory::withClips(1)->create();
    $file = UploadedFile::fake()->create('video.mp4', 1000);
    $response = $this->opencastService->ingestMediaPackage($series->clips()->first(), $file);

    expect($response->getStatusCode())->toEqual(200);
});

it('logs the exception message if server is not available for ingesting a media package', function () {
    $series = SeriesFactory::withOpencastID()->create();

    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );
    $clip = Clip::factory()->create();
    $file = UploadedFile::fake()->create('video.mp4', 1000);
    $this->opencastService->ingestMediaPackage($clip, $file);

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('fetch running workflows for a given series', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append($this->mockSeriesRunningWorkflowsResponse($series, true));
    $response = $this->opencastService->getEventsByStatus(OpencastWorkflowState::RUNNING, $series);

    expect($response[0]['processing_state'])->toEqual('RUNNING');
    expect($response[0]['is_part_of'])->toEqual($series->opencast_series_id);
});

it('fetches an empty collection for running workflows if no opencast server is available', function () {
    $series = SeriesFactory::create();
    $this->mockHandler->append($this->mockServerNotAvailable());
    $seriesInfo = $this->opencastService->getEventsByStatus(OpencastWorkflowState::RUNNING);

    expect($seriesInfo->isEmpty())->toBeTrue();
});

it('fetch failed events for a given series', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append($this->mockEventResponse(
        $series,
        OpencastWorkflowState::FAILED
    ));
    $response = $this->opencastService->getEventsBySeries($series, OpencastWorkflowState::FAILED);

    expect($response->pluck('processing_state')->contains('FAILED'))->toBeTrue();
    expect($response->first()['is_part_of'])->toEqual($series->opencast_series_id);
});

it('fetch events waiting for trimming', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append($this->mockTrimmingEventsResponse($series));

    $response = $this->opencastService->getEventsWaitingForTrimming();

    expect($response)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($response)->not->toBeEmpty();
});

it('logs the exception message if server is not available for getting events waiting for trimming', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->opencastService->getEventsWaitingForTrimming($series);

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('update event requires an opencast event as an array otherwise it throws an error', function () {
    $this->expectException(ArgumentCountError::class);
    $this->opencastService->updateEvent();
});

it('logs the exception message if server is not available updating an event', function () {
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $event = [
        'identifier' => 'a131d2e2-9de2-40cb-9716-af9824055f4a',
        'title' => 'An Opencast Event',
    ];
    $this->opencastService->updateEvent($event);

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('updates opencast series title', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append($this->mockNoResultsResponse());
    $response = $this->opencastService->updateSeries($series);

    expect($response->getStatusCode())->toEqual(200);
});

it('logs the exception message if server is not available updating a series', function () {
    $this->mockHandler->append(
        $this->mockServerNotAvailable()
    );
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $series = SeriesFactory::create();
    $this->opencastService->updateSeries($series);

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('updates an Opencast ACL for a given series', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockHealthResponse(), // health
        $this->mockSeriesMetadata($series), // seriesInfo
        $this->mockNoResultsResponse(), // getSeriesTheme
        $this->mockNoResultsResponse(), // recording
        $this->mockNoResultsResponse(), // running
        $this->mockNoResultsResponse(), // scheduled
        $this->mockNoResultsResponse(), // failed
        $this->mockNoTrimmingResultsResponse(), // trimming
        $this->mockNoResultsResponse(), // upcoming
        $this->mockNoResultsResponse(), // acl updated successfully
    );
    $user = User::factory()->create();
    $opencastSeriesInfo = $this->opencastService->getSeriesInfo($series);
    $response = $this->opencastService->updateSeriesAcl($series, $opencastSeriesInfo, $user->username, 'addUser');

    expect($response->getStatusCode())->toEqual(200);
});

it('logs the exception message if server is not available for updating series ACL', function () {
    $series = SeriesFactory::withOpencastID()->create();

    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );
    $user = User::factory()->create();
    $opencastSeriesInfo = collect();
    $this->opencastService->updateSeriesAcl($series, $opencastSeriesInfo, $user->username, 'addUser');

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('formats the acl form data and remove the username if the action is removeUser', function () {
    $opencastSeriesInfo = collect([
        'metadata' => [
            'acl' => [
                0 => [
                    'allow' => true,
                    'role' => 'ROLE_ADMIN',
                    'action' => 'read',
                ],
                1 => [
                    'allow' => true,
                    'role' => 'ROLE_ADMIN',
                    'action' => 'write',
                ],
                2 => [
                    'allow' => true,
                    'role' => 'ROLE_USER_ADMIN',
                    'action' => 'read',
                ],
                3 => [
                    'allow' => true,
                    'role' => 'ROLE_USER_ADMIN',
                    'action' => 'write',
                ],
                4 => [
                    'allow' => true,
                    'role' => 'ROLE_USER_TEST001',
                    'action' => 'read',
                ],
                5 => [
                    'allow' => true,
                    'role' => 'ROLE_USER_TEST001',
                    'action' => 'write',
                ],
            ],
        ],
    ]);
    $response = $this->opencastService->updateSeriesAclFormData($opencastSeriesInfo, 'test001', 'removeUser');
    expect($response['form_params']['acl'])
        ->toBe('[{"allow":true,"role":"ROLE_ADMIN","action":"read"},'.
        '{"allow":true,"role":"ROLE_ADMIN","action":"write"},{"allow":true,"role":"ROLE_USER_ADMIN",'.
        '"action":"read"},{"allow":true,"role":"ROLE_USER_ADMIN","action":"write"}]');
});

it('fetches an empty collection for failed events if no opencast server is available', function () {
    $series = SeriesFactory::create();
    $this->mockHandler->append($this->mockServerNotAvailable());
    $seriesInfo = $this->opencastService->getEventsBySeries($series, OpencastWorkflowState::FAILED);

    expect($seriesInfo->isEmpty())->toBeTrue();
});

test('if fetches a collection of all finished events for a given series', function () {
    $series = SeriesFactory::withClips(1)->withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockEventResponse($series, OpencastWorkflowState::SUCCEEDED),
        $this->mockEventResponse($series, OpencastWorkflowState::STOPPED)
    );
    $response = $this->opencastService->getProcessedEventsBySeriesID($series->opencast_series_id);

    expect($response)->toBeInstanceOf(Collection::class);
    expect($response->pluck('processing_state')->contains('STOPPED'))->toBeTrue();
    expect($response->pluck('processing_state')->contains('SUCCEEDED'))->toBeTrue();
    expect($response->first()['is_part_of'])->toEqual($series->opencast_series_id);
});

it('fetches a collection of all assets for a given event', function () {
    $this->mockHandler->append(
        $this->mockEventAssets($this->faker->uuid(), $this->faker->uuid()),
        $this->mockEventAssets($this->faker->uuid(), $this->faker->uuid())
    );
    $response = $this->opencastService->getAssetsByEventID($this->faker->uuid);

    expect($response)->toBeInstanceOf(Collection::class);
});

it('fetches all metadata for a single event', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append($this->mockEventResponse($series, OpencastWorkflowState::SUCCEEDED));
    $response = $this->opencastService->getEventByEventID($this->faker->uuid());

    expect($response)->toBeInstanceOf(Collection::class);
});

it('formats data for opencast create user post request', function () {
    $user = User::factory()->create();
    $data = [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'form_params' => [
            'username' => $user->username,
            'password' => $password = Str::random(10),
            'name' => $user->getFullNameAttribute(),
            'email' => $user->email,
            'roles' => "[{'name': 'ROLE_GROUP_MMZ_HIWIS', 'type': 'INTERNAL'}]",
        ],
    ];

    expect($this->opencastService->createAdminUserFormData($user, $password))->toEqual($data);
});

it('formats data for opencast create series post request', function () {
    $series = SeriesFactory::create();
    $title = "{$series->title} / tidesSeriesID: {$series->id}";
    $data = [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'form_params' => [
            'metadata' => '[{"flavor": "dublincore/series","title": "EVENTS.EVENTS.DETAILS.CATALOG.EPISODE",
                        "fields": [
                        {
                             "id": "title",
                             "value": "'.$title.'",
                         },
                         {
                             "id": "creator",
                             "value": ["'.$series->owner->name.'"],
                         },
                         ]
                    }]',
            'acl' => '[
					{"allow": true,"role": "ROLE_ADMIN","action": "read"},
				    {"allow": true,"role": "ROLE_ADMIN","action": "write"},
				    {"allow": true,"role": "ROLE_USER_ADMIN","action": "read"},
				    {"allow": true,"role": "ROLE_USER_ADMIN","action": "write"},
			    ]',
            'theme' => config('opencast.default_theme_id'),
        ],
    ];

    expect($this->opencastService->createSeriesFormData($series))->toEqual($data);
});

it('formats data for opencast ingest media package request', function () {
    Storage::fake('videos');
    $series = SeriesFactory::withClips(2)->create(['opencast_series_id' => Str::uuid()]);
    $file = UploadedFile::fake()->create('video.mp4', 1000);
    $data = [
        'multipart' => [
            [
                'name' => 'flavor',
                'contents' => 'presenter/source',
            ],
            [
                'name' => 'title',
                'contents' => $series->clips()->first()->title,
            ],
            [
                'name' => 'description',
                'contents' => $series->clips()->first()->id,
            ],
            [
                'name' => 'publisher',
                'contents' => $series->clips()->first()->owner->email,
            ],
            [
                'name' => 'isPartOf',
                'contents' => $series->opencast_series_id,
            ],
            [
                'name' => 'file',
                'contents' => file_get_contents($file),
                'filename' => basename($file),
            ],
        ],
    ];

    expect($this->opencastService
        ->ingestMediaPackageFormData($series->clips()->first(), $file))->toEqual($data);
});

it('logs the exception message if server is not available for getting processed events by series', function () {
    $series = SeriesFactory::withOpencastID()->create();

    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
        $this->mockServerNotAvailable(), // health
    );

    $this->opencastService->getProcessedEventsBySeriesID($series->opencast_series_id);

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('fetching processed events will fetch those events that have either succeeded or stopped status ', function () {
    $series = SeriesFactory::withOpencastID()->create();
    $this->mockHandler->append(
        $this->mockEventResponse($series, OpencastWorkflowState::SUCCEEDED), // health
        $this->mockEventResponse($series, OpencastWorkflowState::STOPPED), // health
    );

    $events = $this->opencastService->getProcessedEventsBySeriesID($series->id);

    expect($events)->toBeInstanceOf(Collection::class);
    expect($events->count())->toBe(2);

});

it('logs the exception message if server is not available for fetching all assets for an event', function () {
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );

    $this->opencastService->getAssetsByEventID(Str::uuid());

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('logs the exception message if server is not available for fetching an event by event id', function () {
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );

    $this->opencastService->getEventByEventID(Str::uuid());

    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });
    expect($containsStatus)->toBeTrue();
});

it('starts ingesting a video by creating a media package', function () {
    $this->mockHandler->append(
        $this->mockCreateMediaPackageResponse(), // health
    );
    $response = $this->opencastService->createMediaPackage();

    expect($response)->toBeString();
});

it('logs the exception message if server is not available for creating a media package', function () {
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );
    $this->opencastService->createMediaPackage();
    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });

    expect($containsStatus)->toBeTrue();
});

it('adds a catalog to create mp xml file', function () {
    $clip = Clip::factory()->create();
    $this->mockHandler->append(
        $this->mockCreateMediaPackageResponse(),
        $this->mockAddCatalogResponse(),
    );
    $mp = $this->opencastService->createMediaPackage();
    $response = $this->opencastService->addCatalog($mp, $clip);

    expect($response)->toBeString();
});

it('logs the exception message if server is not available for adding a dc catalog', function () {
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );
    $this->opencastService->addCatalog('', Clip::factory()->create());
    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });

    expect($containsStatus)->toBeTrue();
});

it('adds a track to create mp xml file', function () {
    $clip = Clip::factory()->create();
    $this->mockHandler->append(
        $this->mockCreateMediaPackageResponse(),
        $this->mockAddCatalogResponse(),
        $this->mockAddTrackResponse(),
    );
    $mp = $this->opencastService->createMediaPackage();
    $mp = $this->opencastService->addCatalog($mp, $clip);
    $file = UploadedFile::fake()->create('video.mp4', 1000);

    $response = $this->opencastService->addTrack($mp, 'presenter/source', $file);

    expect($response)->toBeString();
});

it('logs the exception message if server is not available for adding a track', function () {
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );
    $file = UploadedFile::fake()->create('video.mp4', 1000);

    $response = $this->opencastService->addTrack('', 'presenter/source', $file);
    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });

    expect($containsStatus)->toBeTrue();
});

it('ingests a media package with a certain workflow', function () {
    $this->mockHandler->append(
        $this->mockCreateMediaPackageResponse(),
        $this->mockAddCatalogResponse(),
        $this->mockAddTrackResponse(),
        $this->mockAddTrackResponse(),
    );
    $mp = $this->opencastService->createMediaPackage();
    $mp = $this->opencastService->addCatalog($mp, Clip::factory()->create());
    $file = UploadedFile::fake()->create('video.mp4', 1000);
    $mp = $this->opencastService->addTrack($mp, 'presenter/source', $file);
    $response = $this->opencastService->ingest($mp, 'fast-workflow');

    expect($response)->toBeString();
});

it('ingests a media package with a default workflow', function () {
    $this->mockHandler->append(
        $this->mockCreateMediaPackageResponse(),
        $this->mockAddCatalogResponse(),
        $this->mockAddTrackResponse(),
        $this->mockAddTrackResponse(),
    );
    $mp = $this->opencastService->createMediaPackage();
    $mp = $this->opencastService->addCatalog($mp, Clip::factory()->create());
    $file = UploadedFile::fake()->create('video.mp4', 1000);
    $mp = $this->opencastService->addTrack($mp, 'presenter/source', $file);
    $response = $this->opencastService->ingest($mp);

    expect($response)->toBeString();
});

it('logs the exception message if server is not available for ingesting a video file', function () {
    $loggedErrors = [];
    Log::shouldReceive('error')
        ->andReturnUsing(function ($message) use (&$loggedErrors) {
            $loggedErrors[] = $message;
        });
    $this->mockHandler->append(
        $this->mockServerNotAvailable(), // health
    );

    $response = $this->opencastService->ingest('', 'fast-workflow');
    // Now assert that at least one of the logged errors contains 'status'
    $containsStatus = collect($loggedErrors)->contains(function ($message) {
        return str_contains($message, 'cURL error 6');
    });

    expect($containsStatus)->toBeTrue();
});

beforeEach(function () {
    $this->mockHandler = $this->swapOpencastClient();
    $this->opencastService = app(OpencastService::class);
});
