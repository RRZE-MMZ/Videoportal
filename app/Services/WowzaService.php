<?php

namespace App\Services;

use App\Enums\Content;
use App\Http\Clients\LiveStreamingClient;
use App\Http\Clients\StreamingClient;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Livestream;
use App\Models\Setting;
use Composer\Pcre\UnexpectedNullMatchException;
use DOMException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\ArrayToXml\ArrayToXml;

class WowzaService
{
    private Response $response;

    private Setting $streamingSettings;

    private StreamingClient $streamingClient;

    private LiveStreamingClient $liveStreamingClient;

    private array $clients;

    public function __construct(
        StreamingClient $streamingClient,
        LiveStreamingClient $liveStreamingClient
    ) {
        $this->streamingClient = $streamingClient;
        $this->liveStreamingClient = $liveStreamingClient;
        $this->clients = [
            'stream' => $streamingClient,
            'livestream' => $liveStreamingClient,
        ];
        $this->response = new Response(200, []);
        $this->streamingSettings = Setting::streaming();
    }

    /**
     * Check whether Wowza Server is online
     */
    public function getHealth(string $connection = 'stream'): Collection
    {
        if (! isset($this->clients[$connection])) {
            throw new InvalidArgumentException("The connection [$connection] is not defined.");
        }
        $response = collect([
            'releaseId' => 'Wowza server not available',
            'status' => 'failed',
        ]);

        try {
            $this->response = $this->clients[$connection]->get('/');
            if (! empty(json_encode((string) $this->response->getBody(), true))) {
                $response->put('releaseId', json_encode((string) $this->response->getBody(), true))
                    ->put('status', 'pass');
            }
        } catch (GuzzleException $e) {
            Log::error($e);
        }

        return $response;
    }

    public function getAllApplications(string $connection = 'stream'): Collection
    {
        if (! isset($this->clients[$connection])) {
            throw new InvalidArgumentException("The connection [$connection] is not defined.");
        }
        $response = collect([
            'releaseId' => 'Wowza server not available',
            'status' => 'failed',
        ]);

        try {
            $this->response = $this->clients[$connection]
                ->get('/v2/servers/_defaultServer_/vhosts/_defaultVHost_/applications');
            if (! empty((string) $this->response->getBody())) {
                $response->put('applications', json_encode(simplexml_load_string((string) $this->response->getBody())));
            }
        } catch (GuzzleException $e) {
            Log::error($e);
        }

        return $response;
    }

    /**
     * Generates smil files for wowza streaming
     *
     * @throws DOMException
     */
    public function createSmilFile(Model $model): void
    {
        // select all clip video assets, iterate them and create an array for array to xml package
        if ($model->assets->isNotEmpty()) {
            $this->generateSmilFile($model, Content::PRESENTER);
            $this->generateSmilFile($model, Content::PRESENTATION);
            $this->generateSmilFile($model, Content::COMPOSITE);
        }
    }

    /**
     * @throws DOMException
     */
    public function generateSmilFile(Model $model, Content $type): void
    {
        $assetsCollection = $model->getAssetsByType($type)->get();
        if ($assetsCollection->isNotEmpty()) {
            $xmlArray['body']['switch'] = $assetsCollection
                ->sortByDesc('height')
                ->map(function ($asset) {
                    return $this->createSmilFileArray($asset);
                })
                ->toArray();

            $result = new ArrayToXml($xmlArray, [
                'rootElementName' => 'smil',
                '_attributes' => [
                    'title' => 'Clip ID:'.$model->id,
                ],
            ], true, 'UTF-8', '1.0', []);

            $original_file_name = str($type->name)->lower().'.smil';
            //store the generated file to clip path
            Storage::disk('videos')
                ->put(getClipStoragePath($model)."/{$original_file_name}", $xmlFile = $result->prettify()->toXml());

            //save or update the smil file in db
            $model->addAsset(Asset::create([
                'disk' => 'videos',
                'original_file_name' => $original_file_name,
                'type' => Content::SMIL(),
                'guid' => Str::uuid(),
                'path' => getClipStoragePath($model),
                'duration' => '0',
                'width' => '0',
                'height' => '0',
            ]));
            $model->has_video_assets = true;
        }
    }

    /**
     * Create a smil array to use for spatie component
     *
     * @return array[]
     */
    public function createSmilFileArray($asset): array
    {
        return [
            'video' => [
                '_attributes' => [
                    'src' => 'mp4:'.$asset->original_file_name,
                    'system-bitrate' => $bitrate = $this->findWowzaAssetBitrate((int) $asset->height),
                    'width' => $asset->width,
                    'height' => $asset->height,
                ],
                'paramVideoBR' => [
                    '_attributes' => [
                        'name' => 'videoBitrate',
                        'value' => $bitrate,
                        'valuetype' => 'data',
                    ],
                ],
                'paramAudioBR' => [
                    '_attributes' => [
                        'name' => 'audioBitrate',
                        'value' => '44100',
                        'valuetype' => 'data',
                    ],
                ],
                'paramVideoCodecID' => [
                    '_attributes' => [
                        'name' => 'videoCodecId',
                        'value' => 'avc1.4d401f',
                        'valuetype' => 'data',
                    ],
                ],
                'paramAudioCodecID' => [
                    '_attributes' => [
                        'name' => 'audioCodecId',
                        'value' => 'mp4a.40.2',
                        'valuetype' => 'data',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Wowza bitrate value based on asset's height
     */
    public function findWowzaAssetBitrate($videoPixelHeight): int
    {
        return match (true) {
            ($videoPixelHeight > 700 && $videoPixelHeight < 800) => 1100000,
            ($videoPixelHeight > 360 && $videoPixelHeight < 700) => 750000,
            ($videoPixelHeight <= 360) => 450000,
            default => 1500000
        };
    }

    public function getDefaultPlayerURL(Clip $clip): array
    {
        $urls = [];
        if ($clip->is_livestream) {
            if ($clip->livestream) {
                $urls['defaultPlayerUrl'] = $this->livestreamSecureUrls($clip->livestream)->first();
            } else {
                $urls['defaultPlayerUrl'] = 'http://172.17.0.2:1935/live/hstream/playlist.m3u8';
            }
        } else {
            $urls['urls'] = $this->vodSecureUrls($clip);

            $urls['defaultPlayerUrl'] = match (true) {
                empty($urls['urls']) => [],
                $urls['urls']->has('composite') => $urls['urls']['composite'],
                $urls['urls']->has('presenter') => $urls['urls']['presenter'],
                $urls['urls']->has('presentation') => $urls['urls']['presentation'],
                default => []
            };
        }

        return $urls;
    }

    public function livestreamSecureUrls(Livestream $livestream): Collection
    {
        $url =
            $this->streamingSettings->data['wowza']['server2']['engine_url'].
            $livestream->content_path.
            $livestream->file_path.'/playlist.m3u8';

        $wowzaContentPath = $livestream->content_path.$livestream->file_path;
        $secureToken = $this->streamingSettings->data['wowza']['server2']['secure_token'];
        $tokenPrefix = $this->streamingSettings->data['wowza']['server2']['token_prefix'];
        $urlWithToken = $this->genToken($tokenPrefix, $wowzaContentPath, $secureToken, $url);

        return collect($urlWithToken);
    }

    /**
     * Generates a wowza streaming link with secure token settings
     */
    public function vodSecureUrls(Clip $clip): Collection
    {
        $filePaths = collect();
        if ($clip->assets->count() > 0) {
            $clip->getAssetsByType(Content::SMIL)->each(function ($asset) use ($filePaths) {
                $fileType = Str::before($asset->original_file_name, '.smil');
                if (config('wowza.check_fautv_links')) {
                    if (Str::contains($asset->original_file_name, 'composite')) {
                        $asset->original_file_name = 'combined.smil';
                    } elseif (Str::contains($asset->original_file_name, 'presenter')) {
                        $asset->original_file_name = 'camera.smil';
                    } else {
                        $asset->original_file_name = 'slides.smil';
                    }
                }
                $url =
                    $this->streamingSettings->data['wowza']['server1']['engine_url'].
                    $this->streamingSettings->data['wowza']['server1']['content_path'].
                    $asset->path.$asset->original_file_name.'/playlist.m3u8';

                $wowzaContentPath =
                    $this->streamingSettings->data['wowza']['server1']['content_path'].
                    $asset->path.
                    $asset->original_file_name;
                $secureToken = $this->streamingSettings->data['wowza']['server1']['secure_token'];
                $tokenPrefix = $this->streamingSettings->data['wowza']['server1']['token_prefix'];
                $urlWithToken = $this->genToken($tokenPrefix, $wowzaContentPath, $secureToken, $url);
                $filePaths->put($fileType, $urlWithToken);
            });
        }

        return $filePaths;
    }

    public function reserveLivestreamRoom(
        ?string $opencastAgentID = '',
        ?Clip $livestreamClip = null,
        ?string $endTime = null,
        ?string $livestreamRoomName = '',
    ): ?Livestream {

        $livestream = $this->findLivestream($opencastAgentID, $livestreamRoomName);
        if (is_null($livestream)) {
            Log::error('No livestream room found for opencast location-> '.$opencastAgentID);

            return null;
        }

        $time_availability_end = is_null($endTime) ? Carbon::now()->addHours(2)
            : Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $endTime)->add('2 hours');

        //livestream is matching with opencast capture agent

        try {
            if ($livestream) {
                //reserve the livestream for this clipID
                $livestream->clip_id = (isset($livestreamClip)) ? $livestreamClip->id : null;
                $livestream->time_availability_start = Carbon::now();
                $livestream->time_availability_end = $time_availability_end;
                $livestream->active = true;
                $livestream->save();
                if (isset($livestreamClip)) {
                    $livestream->clip->recordActivity('Enabled livestream room - '.$livestream->name);
                }
                $livestream->recordActivity('Enabled livestream room - '.$livestream->name);
            }

            return $livestream;
        } catch (UnexpectedNullMatchException $exception) {
            Log::error($exception);
        }
    }

    private function genToken(mixed $tokenPrefix, string $wowzaContentPath, mixed $secureToken, string $url): string
    {
        $tokenStartTime = $tokenPrefix.'starttime='.time();
        $tokenEndTime = $tokenPrefix.'endTime='.(time() + 21600);

        $userIP = (App::environment(['testing', 'local'])) ? env('FAUTV_USER_IP') : $_SERVER['REMOTE_ADDR'];
        $hashStr = "{$wowzaContentPath}?{$userIP}&{$secureToken}&{$tokenEndTime}&{$tokenStartTime}";
        $hash = hash('sha256', $hashStr, 1);
        $usableHash = strtr(base64_encode($hash), '+/', '-_');
        $urlWithToken = "{$url}?{$tokenStartTime}&{$tokenEndTime}&{$tokenPrefix}hash={$usableHash}";

        return $urlWithToken;
    }

    private function findLivestream(?string $opencastAgentID, ?string $livestreamRoomName): ?Livestream
    {
        if (! empty($opencastAgentID)) {
            return checkOpencastLivestreamRoom($opencastAgentID);
        }
        if (! empty($livestreamRoomName)) {
            return Livestream::where('name', 'like', '%'.$livestreamRoomName.'%')->first();
        }

        return null;
    }
}
