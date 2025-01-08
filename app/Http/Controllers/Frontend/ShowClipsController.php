<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Content;
use App\Enums\OpencastWorkflowState;
use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Setting;
use App\Services\OpencastService;
use App\Services\WowzaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

class ShowClipsController extends Controller
{
    /**
     * Indexes all portal clips
     */
    public function index(): View
    {
        return view('frontend.clips.index');
    }

    /*
     * Clip main page
     *
     *
     * @throws AuthorizationException
     */
    public function show(Clip $clip, WowzaService $wowzaService, OpencastService $opencastService): View
    {
        $this->authorize('view-clips', $clip);
        $assetsResolutions = $clip->assets->map(function ($asset) {
            return match (true) {
                $asset->width >= 1920 => 'QHD',
                $asset->width >= 720 && $asset->width < 1920 => 'HD',
                $asset->width >= 10 && $asset->width < 720 => 'SD',
                $asset->type == Content::AUDIO() => 'Audio',
                default => 'PDF/CC'
            };
        })
            ->unique()
            ->filter(function ($value, $key) {
                return $value !== 'PDF/CC';
            });

        $transcodingVideoUrl = ($clip->opencast_event_id)
            ? $this->checkForActiveTranscodingJob(clip: $clip, opencastService: $opencastService)
            : false;

        $wowzaStatus = $wowzaService->getHealth();
        $urls = ($wowzaStatus->has('status') && $wowzaStatus->get('status') !== 'failed')
            ? $wowzaService->getDefaultPlayerURL($clip)
            : collect([
                'defaultPlayerUrl' => getProtectedUrl($clip->assets()->formatVideo()->first()?->path),
            ]);

        return view('frontend.clips.show', [
            'clip' => $clip,
            'wowzaStatus' => $wowzaService->getHealth(),
            'defaultVideoUrl' => $urls['defaultPlayerUrl'],
            'alternativeVideoUrls' => isset($urls['urls']) ? $urls['urls'] : [],
            'previousNextClipCollection' => $clip->previousNextClipCollection(),
            'assetsResolutions' => $assetsResolutions,
            'playerSetting' => Setting::portal(),
            'transcodingVideoUrl' => $transcodingVideoUrl,
        ]);
    }

    private function checkForActiveTranscodingJob(Clip $clip, OpencastService $opencastService): string|bool
    {
        $event = $opencastService->getEventByEventID($clip->opencast_event_id);
        if ($event->get('status') === OpencastWorkflowState::RUNNING->value) {
            $settings = Setting::portal();

            return getProtectedUrl($settings->data['player_transcoding_video_file_path']);
        } else {
            return false;
        }
    }
}
