<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Content;
use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Series;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ShowSeriesController extends Controller
{
    public function index(): View
    {
        return view('frontend.series.index');
    }

    /**
     * Series public page
     *
     * @throws AuthorizationException
     */
    public function show(Series $series): View
    {
        $this->authorize('view-series', $series);

        $clips = [];
        $chapters = $series->chapters();
        /*
         * for visitors fetch only clips that containing a video asset
         */
        if ($chapters->count() > 0) {
            // clips info will be eager loaded inside the chapters collection
            $chapters =
                (auth()->user()?->id === $series->owner_id || auth()->user()?->isAdmin())
                    ? $chapters->orderBy('position')->get()
                    : $chapters->withWhereHas('clips', function ($query) {
                        $query->has('assets')->orWhere('is_livestream', true);
                    })->orderBy('position')->get();

            $assetsResolutions = $chapters->flatMap(fn ($chapter) => $chapter->clips->flatMap(function ($clip) {
                return $clip->assets->map(function ($asset) {
                    return match (true) {
                        $asset->width >= 1920 => 'QHD',
                        $asset->width >= 720 && $asset->width < 1920 => 'HD',
                        $asset->width >= 10 && $asset->width < 720 => 'SD',
                        $asset->type == Content::AUDIO() => 'Audio',
                        default => 'PDF/CC'
                    };
                })->unique();
            }))->flatten()
                ->unique()
                ->filter(function ($value, $key) {
                    return $value !== 'PDF/CC';
                });
        } else {
            $chapters = $series->chapters()->get();
            $clips = (($series->owner_id && auth()->user()?->id === $series->owner_id) || auth()->user()?->isAdmin())
                ?
                Clip::select(['id', 'title', 'slug', 'episode', 'is_public', 'recording_date'])
                    ->where('series_id', $series->id)
                    ->WithSemester()
                    ->with('assets:id,width,height,type')
                    ->orderBy('episode')->get()
                :
                Clip::select(['id', 'title', 'slug', 'episode', 'is_public', 'recording_date'])
                    ->where(function (Builder $query) {
                        $query->has('assets')
                            ->orWhere('recording_date', '<', Carbon::yesterday())
                            ->orWhere('is_livestream', true);
                    })
                    ->where('series_id', $series->id)
                    ->where('is_public', true)
                    ->WithSemester()
                    ->with('assets:id,width,height,type')
                    ->orderBy('episode')
                    ->get();
            $assetsResolutions = $clips
                ->map(function ($clip) {
                    return $clip->assets->map(function ($asset) {
                        return match (true) {
                            $asset->width >= 1920 => 'QHD',
                            $asset->width >= 720 && $asset->width < 1920 => 'HD',
                            $asset->width >= 10 && $asset->width < 720 => 'SD',
                            $asset->type == Content::AUDIO() => 'Audio',
                            default => 'PDF/CC'
                        };
                    })->unique();
                })
                ->flatten()
                ->unique()
                ->filter(function ($value, $key) {
                    return $value !== 'PDF/CC';
                });
        }

        return view('frontend.series.show', compact(['series', 'clips', 'chapters', 'assetsResolutions']));
    }
}
