<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Acl;
use App\Enums\OpencastWorkflowState;
use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesOpencastActionsRequest;
use App\Models\Clip;
use App\Models\Semester;
use App\Models\Series;
use App\Models\Setting;
use App\Services\OpencastService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;

use function PHPUnit\Framework\isEmpty;

class SeriesVideoWorkflowController extends Controller
{
    /**
     * Creates an Openast series for the given tides series
     *
     *
     * @throws AuthorizationException
     */
    public function createSeries(Series $series, OpencastService $opencastService): RedirectResponse
    {
        $opencastSeriesInfo = $opencastService->getSeriesInfo($series);
        if (isEmpty($opencastSeriesInfo->get('metadata'))) {
            $opencastSeriesId = $opencastService->createSeries($series);
            $series->updateOpencastSeriesId($opencastSeriesId);
        }

        session()->flash('flashMessage', 'Opencast series created successfully');

        return to_route('series.edit', $series);
    }

    /**
     * @throws AuthorizationException
     */
    public function updateAcl(
        Series $series,
        SeriesOpencastActionsRequest $request,
        OpencastService $opencastService
    ): RedirectResponse {
        $opencastSeriesInfo = $opencastService->getSeriesInfo($series);

        //Opencast doesn't allow to update a series when a workflow is running
        if ($opencastSeriesInfo->get('running')->isNotEmpty()) {
            session()->flash('flashMessage', 'Opencast workflows running in this series. Try again later');

            return to_route('series.edit', $series);
        }

        $validated = $request->validated();

        $response =
            $opencastService->updateSeriesAcl(
                $series,
                $opencastSeriesInfo,
                $validated['username'],
                $validated['action']
            );
        $opencastAcls = collect(json_decode((string) $response->getBody(), true));

        if ($opencastAcls->isNotEmpty()) {
            session()->flash('flashMessage', 'Opencast acls updated successfully');
        } else {
            session()->flash('flashMessage', 'There was a problem updating Opencast Acls');
        }

        return to_route('series.edit', $series);
    }

    public function updateEventsTitle(
        Series $series,
        SeriesOpencastActionsRequest $request,
        OpencastService $opencastService
    ): RedirectResponse {
        $request->validated();

        $events = $opencastService->getEventsBySeries($series);

        $events->each(function ($event) use ($opencastService) {
            $opencastService->updateEvent($event);
        });

        session()->flash('flashMessage', "{$events->count()} Opencast events updated successfully");

        return to_route('series.edit', $series);
    }

    public function addScheduledEventsAsClips(
        Series $series,
        SeriesOpencastActionsRequest $request,
        OpencastService $opencastService
    ): RedirectResponse {
        $request->validated();

        $events = $opencastService->getEventsByStatus(OpencastWorkflowState::SCHEDULED, $series);
        $clipIdentifiers = $series->clips->pluck('opencast_event_id');
        $events->each(function (array $event, int $key) use ($series, $clipIdentifiers) {
            //search if a clip exists for an event. If so then don't insert to avoid duplicates
            if ($clipIdentifiers->doesntContain($event['identifier'])) {
                $setting = Setting::portal();
                $settingData = $setting->data;
                $attributes = [
                    'owner_id' => $series->owner_id,
                    'semester_id' => Semester::current(zuluToCEST($event['start']))->first()->id,
                    'organization_id' => $series->organization_id,
                    'language_id' => 4,
                    'context_id' => 22,
                    'format_id' => 11,
                    'type_id' => 11,
                    'series_id' => $series->id,
                    'supervisor_id' => auth()->user()->id,
                    'title' => $event['title'],
                    'password' => $series->password,
                    'episode' => $key + 1,
                    'image_id' => $settingData['default_image_id'],
                    'is_public' => true,
                    'recording_date' => zuluToCEST($event['start']),
                    'opencast_event_id' => $event['identifier'],
                ];
                $clip = Clip::create($attributes);
                if (! is_null($series->lms_link)) {
                    $clip->addAcls(collect([Acl::LMS()]));

                } else {
                    $clip->addAcls(collect([Acl::PORTAL()]));
                }
            }
        });
        session()->flash('flashMessage', "{$events->count()} Clips created");

        return to_route('series.edit', $series);
    }
}
