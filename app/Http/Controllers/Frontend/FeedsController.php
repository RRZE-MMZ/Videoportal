<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Content;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Series;
use App\Models\Setting;
use Debugbar;
use DOMException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

class FeedsController extends Controller
{
    /**
     * @return Application|ResponseFactory|Response
     *
     * @throws DOMException
     */
    public function series(Series $series, string $assetsResolution)
    {
        Debugbar::disable();
        $setting = Setting::portal();
        $array = [
            'channel' => [
                'title' => $series->title.' Series ID:'.$series->id,
                'link' => route('frontend.series.feed', [$series, $assetsResolution]),
                'language' => $series->clipsLanguageCode(),
                'copyright' => Carbon::now()->year.' Tides Portal',
                'itunes:author' => is_null($series->presenters->first()?->getFullNameAttribute())
                        ? ''
                        : $series->presenters->first()->getFullNameAttribute(),
                'description' => $series->description,
                'itunes:type' => 'serial',
                'itunes:owner' => [
                    'itunes:name' => $setting->data['feeds_default_owner_name'],
                    'itunes:email' => $setting->data['feeds_default_owner_email'],
                ],
                'itunes:image' => 'test',
                'itunes:summary' => $series->description,
                'itunes:category' => [
                    '_attributes' => [
                        'text' => 'Education',
                    ],
                ],
                'itunes:keywords' => 'Tides Portal, Tides',
                'itunes:explicit' => 'no',
            ],
        ];

        $count = 0;
        $series->clips
            ->filter(fn ($clip) => $clip->assets()->count() && $clip->is_public)
            ->each(function ($clip) use (&$array, &$count, $assetsResolution) {
                $asset = $clip->assets->filter(function ($asset) use ($assetsResolution) {
                    return $this->assetCheck($asset, $assetsResolution);
                })->first();

                $array['channel']['__custom:item:'.$count] = [
                    'title' => $clip->title.' ClipID:'.$clip->id,
                    'itunes:duration' => $asset->durationToHours(),
                    'enclosure' => [
                        '_attributes' => [
                            'url' => route('assets.download', $asset),
                            'length' => $asset->duration,
                            'type' => ($asset->type === Content::AUDIO()) ? 'audio/mp3' : 'video/mp4',
                        ],
                    ],
                    'guid' => [
                        '_attributes' => [
                            'isPermaLink' => 'false',
                        ],
                        '_value' => 'localhost', // need to be filled with asset link
                    ],
                    'pubDate' => $clip->recording_date,
                    'itunes:explicit' => 'false',
                ];
                $count++;
            });

        $arrayToXml = new ArrayToXml($array, [
            'rootElementName' => 'rss',
            '_attributes' => [
                'version' => '2.0',
                'xmlns:itunes' => 'http://www.itunes.com/dtds/podcast-1.0.dtd',
                'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
            ],
        ], true, 'UTF-8');
        $arrayToXml->setDomProperties(['formatOutput' => true]);

        return response($arrayToXml->prettify()->toXml(), 200, ['Content-Type' => 'application/xml']);
    }

    private function assetCheck(Asset $asset, string $assetsResolution)
    {
        return match (true) {
            ($assetsResolution === 'QHD' && $asset->width >= 1920) => $asset,
            ($assetsResolution === 'HD' && ($asset->width >= 720 && $asset->width < 1920)) => $asset,
            ($assetsResolution === 'SD' && ($asset->width >= 10 && $asset->width < 720)) => $asset,
            ($assetsResolution === 'Audio' && $asset->type == Content::AUDIO()) => $asset,
            default => false,
        };
    }

    public function clips(Clip $clip, string $assetsResolution)
    {
        Debugbar::disable();

        $setting = Setting::portal();

        $array = [
            'channel' => [
                'title' => $clip->title.' Clip ID:'.$clip->id,
                'link' => route('frontend.series.feed', [$clip, $assetsResolution]),
                'language' => $clip->language->code,
                'copyright' => Carbon::now()->year.' Tides Portal',
                'itunes:author' => (is_null($clip->presenters->first()?->getFullNameAttribute()))
                    ? ''
                    : $clip->presenters->first()->getFullNameAttribute(),
                'description' => $clip->description,
                'itunes:type' => 'episodic',
                'itunes:owner' => [
                    'itunes:name' => $setting->data['feeds_default_owner_name'],
                    'itunes:email' => $setting->data['feeds_default_owner_email'],
                ],
                'itunes:image' => 'test',
                'itunes:summary' => $clip->description,
                'itunes:category' => [
                    '_attributes' => [
                        'text' => 'Education',
                    ],
                ],
                'itunes:keywords' => 'Tides Portal, Tides',
                'itunes:explicit' => 'no',
            ],
        ];

        $count = 0;
        $clip->assets
            ->each(function ($asset) use (&$array, &$count, $assetsResolution, $clip) {
                $asset = $this->assetCheck($asset, $assetsResolution);

                if ($asset) {
                    $array['channel']['__custom:item:'.$count] = [
                        'title' => $clip->title.' ClipID:'.$clip->id,
                        'itunes:duration' => $asset->durationToHours(),
                        'enclosure' => [
                            '_attributes' => [
                                'url' => route('assets.download', $asset),
                                'length' => $asset->duration,
                                'type' => ($asset->type === Content::AUDIO()) ? 'audio/mp3' : 'video/mp4',
                            ],
                        ],
                        'guid' => [
                            '_attributes' => [
                                'isPermaLink' => 'false',
                            ],
                            '_value' => 'localhost', // need to be filled with asset link
                        ],
                        'pubDate' => $clip->recording_date,
                        'itunes:explicit' => 'false',
                    ];
                    $count++;
                }
            });

        $arrayToXml = new ArrayToXml($array, [
            'rootElementName' => 'rss',
            '_attributes' => [
                'version' => '2.0',
                'xmlns:itunes' => 'http://www.itunes.com/dtds/podcast-1.0.dtd',
                'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
            ],
        ], true, 'UTF-8');
        $arrayToXml->setDomProperties(['formatOutput' => true]);

        return response($arrayToXml->prettify()->toXml(), 200, ['Content-Type' => 'application/xml']);
    }
}
