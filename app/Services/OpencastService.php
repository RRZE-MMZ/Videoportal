<?php

namespace App\Services;

use App\Enums\OpencastWorkflowState;
use App\Http\Clients\OpencastClient;
use App\Models\Clip;
use App\Models\Series;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewOpencastUserAccountCreated;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Spatie\ArrayToXml\ArrayToXml;
use Storage;

class OpencastService
{
    private Response $response;

    private Setting $opencastSettings;

    public function __construct(private OpencastClient $client)
    {
        // initialize an empty response
        $this->response = new Response(200, [], json_encode([]));
        $this->opencastSettings = Setting::opencast();
    }

    /**
     * Fetch all relevant info for a given series like running, failed workflows, etc.
     */
    public function getSeriesInfo(Series $series): Collection
    {
        $opencastSeriesInfo = collect();
        $health = $this->getHealth();
        $opencastSeriesInfo->put('health', $health);
        if ($health->contains('pass')) {
            $opencastSeriesInfo->put('metadata', $this->getSeries($series))
                ->put('theme', $this->getSeriesTheme($series))
                ->put(
                    OpencastWorkflowState::RECORDING->lower(),
                    $this->getEventsByStatus(OpencastWorkflowState::RECORDING, $series)
                )
                ->put(
                    OpencastWorkflowState::RUNNING->lower(),
                    $this->getEventsByStatus(OpencastWorkflowState::RUNNING, $series)
                )
                ->put(
                    OpencastWorkflowState::SCHEDULED->lower(),
                    $this->getEventsByStatusAndByDate(
                        OpencastWorkflowState::SCHEDULED,
                        $series,
                        Carbon::now()->startOfDay(),
                        Carbon::now()->endOfDay()
                    )
                )
                ->put(
                    OpencastWorkflowState::FAILED->lower(),
                    $this->getEventsByStatus(OpencastWorkflowState::FAILED, $series)
                )
                ->put(OpencastWorkflowState::TRIMMING->lower(), $this->getEventsWaitingForTrimming($series))
                ->put('upcoming', $this->getEventsByStatusAndByDate(
                    OpencastWorkflowState::SCHEDULED,
                    $series,
                    Carbon::now()->add('1 day')->startOfDay(),
                    Carbon::now()->add('6 months')->endOfDay()
                ));
        }

        return $opencastSeriesInfo;
    }

    /**
     *  Return a collection of Opencast admin node status
     */
    public function getHealth(): Collection
    {
        $response = collect([
            'releaseId' => 'Opencast server not available',
            'description' => 'unknown',
            'serviceId' => '127.0.0.1',
            'version' => null,
            'status' => 'failed',
        ]);

        try {
            $this->response = $this->client->get('info/health');
            if (! empty(json_decode((string) $this->response->getBody(), true))) {
                $response = collect(json_decode((string) $this->response->getBody(), true));
            }
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $response;
    }

    public function getSeries(Series $series): Collection
    {
        $seriesInfo = collect();

        if (is_null($series->opencast_series_id) || $series->opencast_series_id === '') {
            return $seriesInfo;
        }

        try {
            $this->response = $this->client->get("api/series/{$series->opencast_series_id}?withacl=true");
            $seriesInfo = collect(json_decode((string) $this->response->getBody(), true));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $seriesInfo;
    }

    public function getSeriesTheme(Series $series): Collection
    {
        $seriesTheme = collect();

        try {
            $this->response = $this->client->get("admin-ng/series/{$series->opencast_series_id}/theme.json");
            $seriesTheme = collect(json_decode((string) $this->response->getBody(), true));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $seriesTheme;
    }

    public function getThemes(): Collection
    {
        $themes = collect();

        try {
            $this->response = $this->client->get('admin-ng/themes/themes.json');
            $themesArr = json_decode((string) $this->response->getBody(), true);
            $themes = collect($themesArr['results']);
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $themes;
    }

    /**
     *  Return opencast events based on status
     */
    public function getEventsByStatus(
        OpencastWorkflowState $state,
        ?Series $series = null,
        int $limit = 20,
    ): Collection {
        $events = collect();

        $filter = (is_null($series?->opencast_series_id))
            ? 'status:'.$state->value
            : 'status:'.$state->value.',is_part_of:'.$series->opencast_series_id;
        try {
            $this->response = $this->client->get('api/events', [
                'query' => [
                    'withscheduling' => 'true',
                    'filter' => $filter,
                    'sort' => 'start_date:ASC',
                    'limit' => $limit,
                ],
            ]);
            $events = collect((json_decode((string) $this->response->getBody(), true)));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $events;
    }

    public function getEventsByStatusAndByDate(
        OpencastWorkflowState $state,
        ?Series $series,
        Carbon $startDate,
        Carbon $endDate,
    ): Collection {
        $events = collect();

        $filter = (is_null($series))
            ? 'status:'.$state->value
            : 'status:'.$state->value.',is_part_of:'.$series->opencast_series_id;

        //        dd('start:'.$startDate->isoFormat('YYYY-MM-DD[T]HH:mm:ss[Z]').'/'.
        //            $endDate->isoFormat('YYYY-MM-DD[T]HH:mm:ss[Z]').','.$filter);
        try {
            $this->response = $this->client->get('api/events', [
                'query' => [
                    'withscheduling' => 'true',
                    'filter' => 'start:'.$startDate->isoFormat('YYYY-MM-DD[T]HH:mm:ss[Z]').'/'.
                        $endDate->isoFormat('YYYY-MM-DD[T]HH:mm:ss[Z]').','.$filter,
                    'sort' => 'start_date:asc',
                    'limit' => '100',
                ],
            ]);
            $events = collect((json_decode((string) $this->response->getBody(), true)));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $events;
    }

    public function getEventsWaitingForTrimming(?Series $series = null): Collection
    {
        $series = (is_null($series)) ? '' : $series->opencast_series_id;
        //     /admin-ng/event/events.json
        //      ?filter=comments:OPEN,status:EVENTS.EVENTS.STATUS.PROCESSED&limit=10&offset=0&sort=start_date:ASC
        $trimmingEvents = collect();

        try {
            $this->response = $this->client->get('admin-ng/event/events.json', [
                'query' => [
                    'filter' => 'status:'.OpencastWorkflowState::SUCCEEDED->value.',comments:OPEN,series:'.$series,
                    'offset' => 0,
                    'sort' => 'start_date:ASC',
                ],
            ]);
            $trimmingEvents = collect((json_decode((string) $this->response->getBody(), true)));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        if ($trimmingEvents->isEmpty()) {
            $trimmingEvents['results'] = [];
        }

        return collect($trimmingEvents['results']);
    }

    public function createUser(User $user): Response|ResponseInterface
    {
        try {
            $opencastUserData = $this->createAdminUserFormData($user, Str::random(10));

            $this->response = $this->client->post('admin-ng/users/', $opencastUserData);

            $user->notify(new NewOpencastUserAccountCreated(
                collect($opencastUserData['form_params']),
                $this->opencastSettings->data['url']
            ));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    /**
     * forms the data to create a user for admin-ui
     */
    public function createAdminUserFormData(User $user, $opencastUserPassword): array
    {
        return [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'username' => $user->username,
                'password' => $opencastUserPassword,
                'name' => $user->getFullNameAttribute(),
                'email' => $user->email,
                'roles' => "[{'name': 'ROLE_GROUP_MMZ_HIWIS', 'type': 'INTERNAL'}]",
            ],
        ];
    }

    /**
     * A post request to create a series in Opencast Admin node
     */
    public function createSeries(Series $series): Response|ResponseInterface
    {
        try {
            $this->response = $this->client->post('api/series', $this->createSeriesFormData($series));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    /**
     * forms the data to create a new series
     */
    public function createSeriesFormData(Series $series): array
    {
        $title = "{$series->title} / tidesSeriesID: {$series->id}";

        return [
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
                             "value": ["'.$series->owner?->name.'"],
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
    }

    public function updateSeries(Series $series): Response|ResponseInterface
    {
        try {
            $this->response =
                $this->client->put(
                    "api/series/{$series->opencast_series_id}",
                    $this->updateSeriesFormData($series)
                );
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    public function updateSeriesTheme(Series $series, $themeID)
    {
        try {
            $this->response =
                $this->client->put(
                    "admin-ng/series/{$series->opencast_series_id}/theme",
                    [
                        'headers' => [
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                        'form_params' => [
                            'themeId' => $themeID,
                        ],
                    ]
                );
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;

    }

    /**
     * forms the data to update an existing series
     */
    public function updateSeriesFormData(Series $series): array
    {
        $title = "{$series->title} / tidesSeriesID: {$series->id}";

        return [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'type' => 'dublincore/series',
                'metadata' => '[{
                        "flavor": "dublincore/series",
                        "title": "Opencast Series DublinCore",
                        "fields": [
                                {
                                     "id": "title",
                                     "value": "'.$title.'",
                                 },
                              ]
                       }]',
            ],
        ];
    }

    public function updateEvent(array $event): Response|ResponseInterface
    {
        try {
            $this->response =
                $this->client->put(
                    "api/events/{$event['identifier']}/metadata?type=dublincore/episode",
                    $this->updateEventsFormData($event)
                );
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    /**
     * forms the data to update an existing series
     */
    public function updateEventsFormData(array $event): array
    {
        $title = removeTrailingNumbers($event['title']);

        return [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'metadata' => ' [{
                                     "id": "title",
                                     "value": "'.$title.'",
                                 }]',
            ],
        ];
    }

    public function updateSeriesAcl(
        Series $series,
        Collection $opencastSeriesInfo,
        string $username,
        string $action
    ): Response|ResponseInterface {
        try {
            $this->response = $this->client->put(
                "api/series/{$series->opencast_series_id}/acl",
                $this->updateSeriesAclFormData($opencastSeriesInfo, $username, $action)
            );
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $this->response;
    }

    public function updateSeriesAclFormData(Collection $opencastSeriesInfo, string $username, string $action): array
    {
        if ($opencastSeriesInfo->isEmpty()) {
            return [];
        }
        $acls = $opencastSeriesInfo->map(function ($item, $key) {
            if ($key === 'metadata') {
                return $item['acl'];
            }
        })->get('metadata');

        if ($action === 'addUser') {
            $read = [
                'allow' => true,
                'role' => 'ROLE_USER_'.strtoupper($username),
                'action' => 'read',
            ];
            $write = [
                'allow' => true,
                'role' => 'ROLE_USER_'.strtoupper($username),
                'action' => 'write',
            ];

            array_push($acls, $read, $write);
        } else {
            foreach ($acls as $key => $acl) {
                if ($acl['role'] === 'ROLE_USER_'.strtoupper($username)) {
                    unset($acls[$key]);
                }
            }
        }

        return [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'acl' => json_encode($acls),
                'override' => true,
            ],
        ];
    }

    /**
     * A post request to ingest a video file and start a workflow in Opencast Admin node
     */
    public function ingestMediaPackage(Clip $clip, string $videoFile): Response|ResponseInterface
    {
        try {
            $this->response = $this->client->post(
                "ingest/addMediaPackage/{$this->opencastSettings->data['upload_workflow_id']}",
                $this->ingestMediaPackageFormData($clip, $videoFile)
            );
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }
        // TODO
        // Create upload file table, store upload information and re-ingest if failed before deleting

        return $this->response;
    }

    /**
     *  forms the data to ingest a video file and start a workflow
     */
    public function ingestMediaPackageFormData(Clip $clip, string $file): array
    {
        return [

            'multipart' => [
                [
                    'name' => 'flavor',
                    'contents' => 'presenter/source',
                ],
                [
                    'name' => 'title',
                    'contents' => $clip->title,
                ],
                [
                    'name' => 'description',
                    'contents' => $clip->id,
                ],
                [
                    'name' => 'publisher',
                    'contents' => $clip->owner->email,
                ],
                [
                    'name' => 'isPartOf',
                    'contents' => $clip->series->opencast_series_id,
                ],
                [
                    'name' => 'file',
                    'contents' => file_get_contents($file),
                    'filename' => basename($file),
                ],
            ],
        ];
    }

    public function createMediaPackage(): string
    {
        $mediaPackage = '';
        try {
            $this->response = $this->client->get('/ingest/createMediaPackage');
            $mediaPackage = ((string) $this->response->getBody());
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $mediaPackage;
    }

    public function addCatalog(string $mediaPackage, Clip $clip): string
    {
        try {
            $this->response = $this->client->post(
                'ingest/addDCCatalog/',
                $this->addDCCatalogFormData($mediaPackage, $clip)
            );
            $mediaPackage = ((string) $this->response->getBody());
            Log::info($mediaPackage);
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $mediaPackage;
    }

    public function addDCCatalogFormData(string $mediaPackage, Clip $clip)
    {
        $xmlArray = [
            'dcterms:creator' => $clip->presenter,
            'dcterms:contributor' => 'MMZ',
            'dcterms:created' => [
                '_attributes' => [
                    'xsi:type' => 'dcterms:W3CDTF',
                ],
                '_value' => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
            ],
            'dcterms:temporal' => [
                '_attributes' => [
                    'xsi:type' => 'dcterms:Period',
                ],
                '_value' => 'start=
                    '.Carbon::now()->subMinute(5)->format('Y-m-d\TH:i:s\Z').
                    '; end='.Carbon::now()->subMinute(3)->format('Y-m-d\TH:i:s\Z').'; scheme=W3C-DTF;',
            ],
            'dcterms:description' => $clip->id,
            'dcterms:subject' => $clip->semester->acronym,
            'dcterms:isPartOf' => $clip->series->opencast_series_id,
            'dcterms:title' => $clip->title,
        ];

        $result = new ArrayToXml($xmlArray, [
            'rootElementName' => 'dublincore',
            '_attributes' => [
                'xmlns' => 'http://www.opencastproject.org/xsd/1.0/dublincore/',
                'xmlns:dcterms' => 'http://purl.org/dc/terms/',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
        ], true, 'UTF-8', '1.0', ['standalone' => false]);

        return [
            'multipart' => [
                [
                    'name' => 'mediaPackage',
                    'contents' => $mediaPackage,
                ],
                [
                    'name' => 'dublinCore',
                    'contents' => $result->prettify()->toXml(),
                ],
            ],
        ];
    }

    public function addTrack(string $mediaPackage, string $flavor, $file): string
    {
        $fileName = match ($flavor) {
            'presenter/source' => 'presenter.mp4',
            'presentation/source' => 'presentation.mp4',
            'captions/source+de' => 'source-de.vtt',
            'captions/source+en' => 'source-en.vtt',
        };
        try {
            $this->response = $this->client->post(
                'ingest/addTrack',
                $this->addTrackFormData($mediaPackage, $flavor, $fileName, $file)
            );
            $mediaPackage = ((string) $this->response->getBody());
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $mediaPackage;
    }

    public function addTrackFormData(string $mediaPackage, string $flavor, string $fileName, string $file)
    {
        return [
            'multipart' => [
                [
                    'name' => 'mediaPackage',
                    'contents' => $mediaPackage,
                ],
                [
                    'name' => 'flavor',
                    'contents' => $flavor,
                ],
                [
                    'name' => $fileName,
                    'contents' => Storage::disk('videos')->get($file),
                    'filename' => $fileName,
                ],
            ],
        ];
    }

    public function ingest(string $mediaPackage, string $workflowDefinitionID = ''): string
    {
        $workflowDefinitionID = (! empty($workflowDefinitionID))
            ? $workflowDefinitionID :
            $this->opencastSettings->data['upload_workflow_id'];
        try {
            $this->response = $this->client->post(
                'ingest/ingest/'.$workflowDefinitionID,
                [
                    'multipart' => [
                        [
                            'name' => 'mediaPackage',
                            'contents' => $mediaPackage,
                        ],
                        [
                            'name' => 'workflowDefinitionId',
                            'contents' => $workflowDefinitionID,
                        ],
                    ],
                ]
            );
            $mediaPackage = collect((json_decode((string) $this->response->getBody(), true)));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $mediaPackage;
    }

    /**
     * Returning a collection with opencast processed and canceled events for a series
     */
    public function getProcessedEventsBySeriesID($seriesID): Collection
    {
        $processedEvents = collect();

        try {
            $processed = $this->client->get('api/events', [
                'query' => [
                    'filter' => "series:{$seriesID},status:".OpencastWorkflowState::SUCCEEDED(),
                    'sort' => 'start_date:ASC',
                ],
            ]);
            $canceled = $this->client->get('api/events', [
                'query' => [
                    'filter' => "series:{$seriesID},status:".OpencastWorkflowState::STOPPED(),
                    'sort' => 'start_date:ASC',
                ],
            ]);
            $collection = collect(json_decode((string) $processed->getBody(), true));

            $processedEvents = $collection->merge(collect(json_decode((string) $canceled->getBody(), true)));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $processedEvents;
    }

    public function getEventsBySeries(Series $series, ?OpencastWorkflowState $state = null): Collection
    {
        $status = '';

        if (! is_null($state)) {
            $status = ",status:{$state->value}";
        }
        $events = collect();
        try {
            $this->response = $this->client->get('api/events', [
                'query' => [
                    'filter' => "series:{$series->opencast_series_id}{$status}",
                    'sort' => 'start_date:ASC',
                ],
            ]);
            $events = collect(json_decode((string) $this->response->getBody(), true));
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return $events;
    }

    /**
     * Fetch all assets for a given event ID as collection
     */
    public function getAssetsByEventID($eventID): Collection
    {
        try {
            $response = $this->client->get("assets/episode/{$eventID}");
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());

            return collect();
        }

        $version = $this->getEventByEventID($eventID)->get('archive_version');

        // change the xml response to xml object
        $xmlResponse = simplexml_load_string((string) $response->getBody());

        // format the response and include the created/modified date
        $dateCreated = (string) $xmlResponse->attributes()->start;

        // isolate the media key from XML Object
        $xmlResponse = (array) $xmlResponse->media;

        // create a collection from the XML Object track value
        $opencastAssets = collect($xmlResponse['track']);

        // return a collection map with uid => delivery tags
        return $opencastAssets->mapWithKeys(function ($element) use ($version, $dateCreated) {
            $extension = match (true) {
                ((string) $element->mimetype === 'audio/mpeg') => '.mp3',
                default => '.m4v',
            };

            return [
                (string) $element->attributes()->id => [
                    'tag' => (string) $element->attributes()->type,
                    'type' => (string) $element->mimetype,
                    'video' => ((string) $element->video->resolution) ?: null,
                    'version' => $version,
                    'date_modified' => Carbon::createFromTimeString($dateCreated)->toDateTimeString(),
                    'name' => $element->attributes()->id.$extension,
                ],
            ];
        });
    }

    /**
     * Fetch event metadata information as a collection
     */
    public function getEventByEventID($eventID): Collection
    {
        try {
            $this->response = $this->client->get("api/events/{$eventID}/?withscheduling=true");
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
        }

        return collect(json_decode((string) $this->response->getBody(), true));
    }
}
