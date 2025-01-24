<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteSeriesClipsRequest;
use App\Http\Requests\MassUpdateClipsRequest;
use App\Http\Requests\StoreClipRequest;
use App\Jobs\MassDeleteClipsJob;
use App\Models\Clip;
use App\Models\Semester;
use App\Models\Series;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SeriesClipsController extends Controller
{
    /**
     *  Show create clip form to assign a clip to series
     *
     *
     * @throws AuthorizationException
     */
    public function create(Series $series): View
    {
        $this->authorize('edit-series', $series);

        return view('backend.seriesClips.create', compact('series'));
    }

    /**
     * Store a clip in a series
     */
    public function store(Series $series, StoreClipRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $clip = $series->addClip($validated);

        $clip->addTags(collect($validated['tags']));
        $clip->addAcls(collect($validated['acls']));

        $request->session()->flash('clip_created_successfully', 'Clip created successfully');

        return to_route('clips.edit', $clip);
    }

    /**
     * @throws AuthorizationException
     */
    public function listSeries(Clip $clip): View
    {
        $this->authorize('edit', $clip);

        return view('backend.seriesClips.listSeries', [
            'clip' => $clip,
        ]);
    }

    /**
     * Assign the given series to the given clip
     *
     *
     * @throws AuthorizationException
     */
    public function assign(Series $series, Clip $clip): RedirectResponse
    {
        $this->authorize('edit-series', $series);

        $clip->episode = $series->clips()->count() + 1;
        $clip->series()->associate($series);
        $clip->save();

        return to_route('clips.edit', $clip);
    }

    /**
     * @throws AuthorizationException
     */
    public function remove(Clip $clip): RedirectResponse
    {
        $this->authorize('edit-clips', $clip);

        $clip->episode = 1;
        $clip->series()->dissociate();
        $clip->save();

        return to_route('clips.edit', $clip);
    }

    public function listClips(Series $series): Factory|View|Application
    {
        $clips = Clip::select('id', 'title', 'slug', 'episode', 'is_public', 'recording_date')
            ->where('series_id', $series->id)
            ->addSelect(
                [
                    'semester' => Semester::select('name')
                        ->whereColumn('id', 'clips.semester_id')
                        ->take(1),
                ]
            )
            ->with('acls')
            ->orderBy('episode')->get();

        return view('backend.seriesClips.reorder', compact('series', 'clips'));
    }

    /**
     * Changes clips episodes for a series
     */
    public function reorder(Series $series, Request $request): RedirectResponse
    {
        $this->authorize('edit-series', $series);

        $validated = $request->validate([
            'episodes' => ['required', 'array'],
            'episodes.*' => ['integer'],
        ]);

        $series->reorderClips(collect($validated['episodes']));

        return to_route('series.edit', $series);
    }

    public function showClipsMetadata(Series $series): Factory|View|Application
    {
        $this->authorize('edit-series', $series);

        $clips = Clip::select('id', 'title', 'slug', 'episode', 'is_public', 'recording_date')
            ->where('series_id', $series->id)
            ->addSelect(
                [
                    'semester' => Semester::select('name')
                        ->whereColumn('id', 'clips.semester_id')
                        ->take(1),
                ]
            )
            ->with('acls')
            ->orderBy('episode')->get();

        return view('backend.seriesClips.showClipsMetadata', compact('series', 'clips'));
    }

    public function updateClipsMetadata(Series $series, MassUpdateClipsRequest $request)
    {
        $validated = $request->validated();

        $clips = $series->clips()->each(function ($clip) use ($validated) {
            $clip->update(Arr::except($validated, ['tags', 'acls', 'presenters']));

            $clip->addTags(collect($validated['tags']));
            $clip->addPresenters(collect($validated['presenters']));
            $clip->addAcls(collect($validated['acls']));
            $clip->recordActivity('update clip via mass update function');
        });

        session()->flash('flashMessage', "{$series->clips->count()} Clips updated successfully");
        $clips = Clip::select('id', 'title', 'slug', 'episode', 'is_public', 'recording_date')
            ->where('series_id', $series->id)
            ->addSelect(
                [
                    'semester' => Semester::select('name')
                        ->whereColumn('id', 'clips.semester_id')
                        ->take(1),
                ]
            )
            ->with('acls')
            ->orderBy('episode')->get();

        $series->recordActivity('Update series clips via mass update');

        return view('backend.seriesClips.showClipsMetadata', compact('series', 'clips'));
    }

    public function deleteMultipleClips(Series $series, MassDeleteSeriesClipsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        MassDeleteClipsJob::dispatch($validated['clip_ids']);

        session()->flash('flashMessage', 'Series clips will be deleted...Please be patient');

        return to_route('series.edit', $series);
    }
}
