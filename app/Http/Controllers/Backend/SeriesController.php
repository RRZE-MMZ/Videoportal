<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeriesRequest;
use App\Http\Requests\UpdateSeriesRequest;
use App\Models\Series;
use App\Models\Setting;
use App\Models\User;
use App\Services\OpencastService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    /**
     * Index all series in admin portal. In case of simple user list only users series
     */
    public function index(): View
    {
        return view('backend.series.index');
    }

    /**
     * Store a series in database
     */
    public function store(StoreSeriesRequest $request, OpencastService $opencastService): RedirectResponse
    {
        $validated = $request->validated();

        $series = auth()->user()->series()->create(Arr::except($validated, ['presenters']));

        $opencastSeriesId = $opencastService->createSeries($series);

        $series->updateOpencastSeriesId($opencastSeriesId);
        $series->addPresenters(collect($validated['presenters']));

        session()->flash('flashMessage', 'Series created successfully');

        return to_route('series.edit', $series);
    }

    /**
     * Create form for a series
     */
    public function create(): View
    {
        return view('backend.series.create');
    }

    /**
     * Edit form for a series
     *
     *
     * @throws AuthorizationException
     */
    public function edit(Series $series, OpencastService $opencastService): View
    {
        $this->authorize('edit', $series);
        $opencastSettings = Setting::opencast();
        $availableAssistants = collect();
        $opencastSeriesInfo = $opencastService->getSeriesInfo($series);
        if ($opencastSeriesInfo->get('health')->get('status') === 'pass' && $series->opencast_series_id !== '') {
            $assistants = User::byRole(Role::ASSISTANT)->get();
            // reject all assistants that are already in opencast series acl
            $availableAssistants = $assistants->reject(function ($admin) use ($opencastSeriesInfo) {
                if ($opencastSeriesInfo->get('metadata')->isNotEmpty()) {
                    foreach ($opencastSeriesInfo['metadata']['acl'] as $acl) {
                        // Opencast return Roles as ROLE_USER_USERNAME, so filter users based on this string
                        if (Str::contains($acl['role'], Str::of($admin->username)->upper())) {
                            return true;
                        }
                    }
                }
            });
        }

        return view('backend.series.edit', compact([
            'series', 'opencastSeriesInfo', 'availableAssistants', 'opencastSettings',
        ]));
    }

    /**
     * Update a single series in the database
     *
     *
     * @throws AuthorizationException
     */
    public function update(
        Series $series,
        UpdateSeriesRequest $request,
        OpencastService $opencastService
    ): RedirectResponse {
        $this->authorize('update-series', $series);

        $validated = $request->validated();
        if (is_null($series->opencast_series_id)) {
            $opencastSeriesId = $opencastService->createSeries($series);

            $series->updateOpencastSeriesId($opencastSeriesId);
        }
        $series->update(Arr::except($validated, ['presenters']));
        $series->addPresenters(collect($validated['presenters']));

        return to_route('series.edit', $series);
    }

    /**
     * Delete a single series
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Series $series): RedirectResponse
    {
        $this->authorize('delete', $series);

        $series->delete();

        return to_route('series.index');
    }
}
