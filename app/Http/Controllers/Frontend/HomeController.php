<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Acl;
use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Series;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the latest series and clips for the home page
     */
    public function __invoke(): View
    {
        $portalSeries = collect([]);
        $openSeries = Series::select('id', 'slug', 'title', 'owner_id')
            ->hasClipsWithAcl(acl: Acl::PUBLIC)
            ->isPublic()
            ->hasClipsWithAssets()
            ->with(['owner', 'presenters'])
            ->withLastPublicClip()
            ->orderByDesc(Clip::select('recording_date')
                ->where('has_video_assets', 1)
                ->where('is_public', 1)
                ->whereColumn('series_id', 'series.id')
                ->limit(1)
                ->orderByDesc('recording_date'))
            ->limit(12)
            ->get();

        if (auth()->user()) {
            $portalSeries = Series::select('id', 'slug', 'title', 'owner_id')
                ->hasClipsWithAcl(acl: Acl::PORTAL)
                ->isPublic()
                ->hasClipsWithAssets()
                ->with(['owner', 'presenters'])
                ->withLastPublicClip()
                ->orderByDesc(Clip::select('recording_date')
                    ->where('has_video_assets', 1)
                    ->where('is_public', 1)
                    ->whereColumn('series_id', 'series.id')
                    ->limit(1)
                    ->orderByDesc('recording_date'))
                ->limit(12)
                ->get();
        }

        return view('frontend.homepage.index', [
            'series' => $openSeries,
            'portalSeries' => $portalSeries,
            'clips' => Clip::select('id', 'slug', 'title', 'recording_date', 'owner_id')
                ->whereRelation('acls', 'acl_id', Acl::PUBLIC) // show only open clips in homepage
                ->with(['presenters'])
                ->public()
                ->withVideoAssets()
                ->single()
                ->orderByDesc('recording_date')
                ->limit(12)
                ->get(),
        ]);
    }
}
