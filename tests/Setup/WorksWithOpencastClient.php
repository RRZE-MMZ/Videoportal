<?php

namespace Tests\Setup;

use App\Enums\OpencastWorkflowState;
use App\Http\Clients\OpencastClient;
use App\Models\Series;
use App\Models\User;
use DOMException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Util\Xml;
use Spatie\ArrayToXml\ArrayToXml;

trait WorksWithOpencastClient
{
    use WithFaker;

    public function swapOpencastClient(): MockHandler
    {
        $mockHandler = new MockHandler;

        $client = new OpencastClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $this->app->instance(OpencastClient::class, $client);

        return $mockHandler;
    }

    public function mockHealthResponse(): Response
    {
        return new Response(200, [], json_encode([
            'releaseId' => '8.10.0',
            'description' => "Opencast node's health status",
            'serviceId' => 'http://localhost:8080',
            'version' => '1',
            'status' => 'pass',
        ]));
    }

    public function mockServerNotAvailable(): RequestException
    {
        return new RequestException(
            'cURL error 6: Could not resolve host',
            new Request('GET', 'localhost:8080')
        );
    }

    public function mockCreateSeriesResponse(): Response
    {
        return new Response(201, [
            'Location' => [
                '0' => 'http://localhost:8080/api/series/'.Str::uuid(),
            ],
        ]);
    }

    public function mockCreateAdminUserResponse(): Response
    {
        return new Response(201, []);
    }

    public function mockNoResultsResponse(): Response
    {
        return new Response(200, []);
    }

    public function mockNoTrimmingResultsResponse(): Response
    {
        return new Response(201, [], json_encode([
            'total' => 0,
            'offset' => 0,
            'count' => 0,
            'limit' => 10,
            'results' => [],
        ]));
    }

    public function mockIngestMediaPackageResponse(): Response
    {
        return new Response(200, [], json_encode([
            new Xml,
        ]));
    }

    /**
     * @return Response
     */
    public function mockSeriesMetadata(Series $series)
    {
        $user = User::factory()->create();

        return new Response(201, [], json_encode([
            'identifier' => $series->opencast_series_id,
            'creator' => 'Administrator',
            'opt_out' => false,
            'created' => '2022-11-21T09:32:34Z',
            'subjects' => [],
            'description' => '',
            'language' => '',
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
                    'role' => 'ROLE_USER_'.Str::upper($user->username),
                    'action' => 'read',
                ],
                5 => [
                    'allow' => true,
                    'role' => 'ROLE_USER_'.Str::upper($user->username),
                    'action' => 'write',
                ],
            ],
            'title' => $series->title,
            'license' => '',
            'organization' => 'mh_default_org',
            'organizers' => [],
            'publishers' => [],
            'contributors' => [],
            'rightsholder' => '',
        ]));
    }

    public function mockgetSeriesTheme()
    {
        return new Response(201, [], json_encode(['100' => 'Default Facutly Intro-Outro TR']));
    }

    /**
     * Opencast single event metadata response
     */
    public function mockEventResponse(
        Series $series,
        OpencastWorkflowState $state,
        int $archiveVersion = 4,
        string $identifier = 'a131d2e2-9de2-40cb-9716-af9824055f4a'
    ): Response {
        return new Response(201, [], json_encode([
            [
                'identifier' => $identifier,
                'creator' => 'Opencast Project Administrator',
                'presenter' => [],
                'created' => Carbon::now()->toIso8601ZuluString(),
                'is_part_of' => $series->opencast_series_id,
                'subjects' => [],
                'start' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
                'description' => '1', // A clip ID that belongs to the series
                'language' => '',
                'source' => '',
                'title' => 'Processed event',
                'processing_state' => $state->name,
                'license' => '',
                'archive_version' => $archiveVersion,
                'contributor' => [],
                'scheduling' => [
                    'agent_id' => 'test-lecture-hall',
                    'inputs' => [],
                    'start' => Carbon::now()->toIso8601ZuluString(),
                    'end' => Carbon::now()->addMinutes(90)->toIso8601ZuluString(),
                ],
                'series' => $series->title,
                'has_previews' => false,
                'location' => '',
                'rightsholder' => '',
                'publication_status' => [],
                'status' => $state->value,
            ],
        ]));
    }

    /**
     * Opencast single event metadata response
     */
    public function mockEventByEventID(
        $eventID,
        OpencastWorkflowState $state,
        int $archiveVersion = 4,
        string $seriesID = 'a131d2e2-9de2-40cb-9716-af9824055f23'
    ): Response {
        return new Response(201, [], json_encode([
            'identifier' => $eventID,
            'creator' => 'Opencast Project Administrator',
            'presenter' => [],
            'created' => '2021-05-10T14:21:00Z',
            'is_part_of' => $seriesID,
            'subjects' => [],
            'start' => '2021-05-10T14:21:21Z',
            'description' => '',
            'language' => '',
            'source' => '',
            'title' => 'Processed event',
            'processing_state' => $state->name,
            'license' => '',
            'archive_version' => $archiveVersion,
            'contributor' => [],
            'scheduling' => [
                'agent_id' => 'test-lecture-hall',
                'inputs' => [],
                'start' => Carbon::now()->toIso8601ZuluString(),
                'end' => Carbon::now()->addMinutes(90)->toIso8601ZuluString(),
            ],
            'series' => $this->faker->sentence(10),
            'has_previews' => false,
            'location' => '',
            'rightsholder' => '',
            'publication_status' => [],
            'status' => $state->value,
        ]));
    }

    /**
     * Opencast update series acls response
     */
    public function mockUpdateAclResponse(): Response
    {
        return new Response(201, [], json_encode([
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
                'role' => 'ROLE_USER_TEST00',
                'action' => 'read',
            ],
            5 => [
                'allow' => true,
                'role' => 'ROLE_USER_TEST00',
                'action' => 'write',
            ],
        ]));
    }

    /**
     * @throws DOMException
     */
    public function mockEventAssets($videoHDAssetID, $audioAssetID): Response
    {
        $output = new ArrayToXml(
            [
                'media' => [
                    'track' => [
                        [
                            '_attributes' => [
                                'id' => $this->faker->uuid(),
                                'type' => 'source/presenter',
                            ],
                            'mimetype' => 'video/mp4',
                            'video' => [
                                'resolution' => '1280x720', ],
                        ],
                        [
                            '_attributes' => [
                                'id' => $videoHDAssetID,
                                'type' => 'final/presenter',
                            ],
                            'mimetype' => 'video/mp4',
                            'video' => [
                                'resolution' => '1280x720', ],
                        ],
                        [
                            '_attributes' => [
                                'id' => $audioAssetID,
                                'type' => 'final/soundfile',
                            ],
                            'mimetype' => 'audio/mpeg',
                        ],
                    ],
                ], ],
            ['rootElementName' => 'mediapackage',
                '_attributes' => [
                    'start' => Carbon::now()->toIso8601ZuluString(),
                ], ],
            true,
            'UTF-8',
            '1.0',
            []
        );

        return new Response(200, [], (
            $output->toXml()
        ));
    }

    public function mockCreateMediaPackageResponse(): Response
    {
        $mp = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $mp .= '<mediapackage xmlns="http://mediapackage.opencastproject.org"';
        $mp .= 'id="7a0a936a-3d7f-4700-881a-7c05e83d91be" start="2024-02-22T13:50:26Z">';
        $mp .= '<media/><metadata/><attachments/><publications/></mediapackage>';

        return new Response(201, [], json_encode($mp));
    }

    public function mockAddCatalogResponse(): Response
    {
        $mp = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $mp .= '<mediapackage xmlns="http://mediapackage.opencastproject.org"';
        $mp .= 'id="5e4cca0e-2f7e-4e38-a86e-ce9092ea74ac" start="2024-02-22T14:03:42Z"><media/><metadata>';
        $mp .= '<catalog id="a1fe2d1a-1b04-4c1f-a4fd-5ee4b3916202" type="dublincore/episode"><mimetype>';
        $mp .= 'text/xml</mimetype><tags/>';
        $mp .= '<url>http://localhost/files/mediapackage/5e4cca0e-2f7ec/a1fe2d16202/dublincore.xml</url>';
        $mp .= '</catalog></metadata><attachments/><publications/></mediapackage>';

        return new Response(201, [], json_encode($mp));
    }

    public function mockAddTrackResponse(): Response
    {
        $mp = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $mp .= '<mediapackage id="e95c7170-9f79-4b13-bba4-413d45e1e2df" start="2024-02-22T14:20:24Z" ';
        $mp .= 'xmlns="http://mediapackage.opencastproject.org"><media>';
        $mp .= '<track id="2761918a-9cde-4d8e-bd4f-ebdb1f1bed4e" type="presenter/source">';
        $mp .= '<mimetype>video/mp4</mimetype>';
        $mp .= '<tags/><url>http://localhost/files/mediapackage/e95c2df/2761918e/presenter.mp4</url><live>false</live>';
        $mp .= '</track></media><metadata>';
        $mp .= '<catalog id="76a91c39-0692-401d-9caa-b3715d9a12db" type="dublincore/episode">';
        $mp .= '<mimetype>text/xml</mimetype><tags/><url>';
        $mp .= 'http://localhost/files/mediapackage/e95c71df/7612db/dublincore.xml</url></catalog></metadata>';
        $mp .= '<attachments/><publications/></mediapackage>';

        return new Response(201, [], json_encode($mp));
    }

    public function mockTrimmingEventsResponse(Series $series): Response
    {
        $workflows = [
            'total' => 1,
            'offset' => 0,
            'count' => 1,
            'limit' => 10,
            'results' => [
                0 => [
                    'end_date' => '2023-05-31T07:05:00Z',
                    'agent_id' => 'test-lecture-hall ',
                    'displayable_status' => 'EVENTS.EVENTS.STATUS.PROCESSED',
                    'needs_cutting' => true,
                    'source' => 'ARCHIVE',
                    'title' => 'Neuroanatomie V-V14 24',
                    'has_open_comments' => true,
                    'has_preview' => true,
                    'technical_presenters' => [],
                    'has_comments' => true,
                    'technical_end' => '2023-05-31T07:05:00Z',
                    'series' => [
                        'id' => $series->opencast_series_id,
                        'title' => $series->title,
                    ],
                    'presenters' => [
                        'Prof. Dr. med. Jürgen Wörl',
                    ],
                    'technical_start' => '2023-05-31T05:55:00Z',
                    'location' => 'SMP_Ulmenweg',
                    'managedAcl' => '',
                    'workflow_state' => 'SUCCEEDED',
                    'id' => 'c06d9e3e-99a9-4a5b-b245-6685be068d03',
                    'start_date' => '2023-05-31T05:55:00Z',
                    'event_status' => 'EVENTS.EVENTS.STATUS.PROCESSED',
                    'publications' => [],
                ],
            ],
        ];

        return new Response(201, [], json_encode($workflows));
    }

    public function mockSeriesRunningWorkflowsResponse(Series $series, bool $multiple = false): Response
    {
        $events = ($multiple) ? [
            [
                'identifier' => Str::uuid(),
                'creator' => 'Administrator',
                'presenter' => [],
                'created' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
                'is_part_of' => $series->opencast_series_id,
                'subjects' => [],
                'start' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
                'description' => '',
                'language' => '',
                'source' => '',
                'title' => $this->faker->sentence,
                'processing_state' => OpencastWorkflowState::RUNNING->name,
                'duration' => 0,
                'license' => '',
                'archive_version' => 1,
                'contributor' => [],
                'series' => 'Numerik II für Ingenieure / tidesSeriesID: 537',
                'has_previews' => false,
                'location' => '',
                'rightsholder' => '',
                'publication_status' => [],
                'status' => OpencastWorkflowState::RUNNING(),
            ],
            [
                'identifier' => Str::uuid(),
                'creator' => 'Administrator',
                'presenter' => [],
                'created' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
                'is_part_of' => $series->opencast_series_id,
                'subjects' => [],
                'start' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
                'description' => '',
                'language' => '',
                'source' => '',
                'title' => $this->faker->sentence,
                'processing_state' => OpencastWorkflowState::RUNNING->name,
                'duration' => 0,
                'license' => '',
                'archive_version' => 1,
                'contributor' => [],
                'series' => 'Numerik II für Ingenieure / tidesSeriesID: 537',
                'has_previews' => false,
                'location' => '',
                'rightsholder' => '',
                'publication_status' => [],
                'status' => OpencastWorkflowState::RUNNING(),
            ],
        ] :
           [[
               'identifier' => Str::uuid(),
               'creator' => 'Administrator',
               'presenter' => [],
               'created' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
               'is_part_of' => $series->opencast_series_id,
               'subjects' => [],
               'start' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
               'description' => '',
               'language' => '',
               'source' => '',
               'title' => $this->faker->sentence,
               'processing_state' => OpencastWorkflowState::RUNNING->name,
               'duration' => 0,
               'license' => '',
               'archive_version' => 1,
               'contributor' => [],
               'series' => 'Numerik II für Ingenieure / tidesSeriesID: 537',
               'has_previews' => false,
               'location' => '',
               'rightsholder' => '',
               'publication_status' => [],
               'status' => OpencastWorkflowState::RUNNING(),
           ]];

        return new Response(201, [], json_encode($events));
    }

    public function mockScheduledEvents(?Series $series, $count, ?Carbon $startDate, ?Carbon $endDate): Response
    {
        $events = [];
        for ($i = 1; $i <= $count; $i++) {
            array_push($events, [
                'identifier' => Str::uuid(),
                'creator' => 'Administrator',
                'presenter' => [],
                'created' => Carbon::now()->addMinutes(1)->toIso8601ZuluString(),
                'is_part_of' => ($series) ? $series->opencast_series_id : Str::uuid(),
                'subjects' => [],
                'start' => ($startDate)
                    ? $startDate->toIso8601ZuluString()
                    : Carbon::now()->addMinutes(10)->toIso8601ZuluString(),
                'description' => '',
                'language' => '',
                'source' => '',
                'title' => $this->faker->sentence,
                'processing_state' => OpencastWorkflowState::SCHEDULED->name,
                'duration' => 0,
                'license' => '',
                'archive_version' => 1,
                'contributor' => [],
                'series' => ($series) ? $series->title : $this->faker->sentence,
                'scheduling' => [
                    'agent_id' => 'test-lecture-hall',
                    'inputs' => [
                        'Channel A',
                        'Channel B',
                    ],
                    'start' => ($startDate)
                        ? $startDate->toIso8601ZuluString()
                        : Carbon::now()->addMinutes(10)->toIso8601ZuluString(),
                    'end' => ($endDate)
                        ? $endDate->toIso8601ZuluString()
                        : Carbon::now()->addMinutes(20)->toIso8601ZuluString(),
                ],
                'has_previews' => false,
                'location' => 'test-lecturer-hall',
                'rightsholder' => '',
                'publication_status' => [],
                'status' => OpencastWorkflowState::SCHEDULED(),
            ]);
        }

        return new Response(201, [], json_encode($events));
    }
}
