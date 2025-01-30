<?php

namespace App\Observers;

use App\Http\Resources\ClipResource;
use App\Models\Clip;
use App\Services\OpenSearchService;

class ClipObserver
{
    public function __construct(
        private OpenSearchService $openSearchService
    ) {}

    public function creating(Clip $clip): void
    {
        if ($clip->time_availability_start !== null) {
            $this->setTimestampsToNearest5thMinute($clip);
        }
    }

    /**
     * Handle the Clip "created" event.
     */
    public function created(Clip $clip): void
    {
        $clip->refresh();

        $clip->folder_id = 'TIDES_ClipID_'.$clip->id;
        if (auth()->user()?->isAdmin()) {
            $clip->supervisor_id = auth()->user()->id;
        }

        $clip->saveQuietly();
        session()->flash('flashMessage', $clip->title.' '.__FUNCTION__.' successfully');

        $this->openSearchService->createIndex(new ClipResource($clip));
    }

    public function updating(Clip $clip): void
    {
        if ($clip->time_availability_start !== null) {
            $this->setTimestampsToNearest5thMinute($clip);
        }
    }

    /**
     * Handle the Clip "updated" event.
     */
    public function updated(Clip $clip): void
    {
        if (auth()->user()?->isAdmin() && $clip->supervisor_id !== auth()->user()->id) {
            $clip->supervisor_id = auth()->user()->id;
            $clip->saveQuietly();
        }

        session()->flash('flashMessage', "{$clip->title} ".__FUNCTION__.' successfully');

        $this->openSearchService->updateIndex(new ClipResource($clip));
    }

    /**
     * Handle the Clip "deleted" event.
     */
    public function deleted(Clip $clip): void
    {
        session()->flash('flashMessage', "{$clip->title} ".__FUNCTION__.' successfully');

        if ($clip->series()->exists()) {
            $clip->series->recordActivity(
                "ClipID:{$clip->id}/{$clip->title}/{$clip->recording_date} deleted",
                ['before' => [], 'after' => []]
            );
        }

        $this->openSearchService->deleteIndex($clip);
    }

    /**
     * Handle the Clip "restored" event.
     */
    public function restored(Clip $clip): void
    {
        session()->flash('flashMessage', "{$clip->title} ".__FUNCTION__.' successfully');
    }

    /**
     * Handle the Clip "force deleted" event.
     */
    public function forceDeleted(Clip $clip): void
    {
        session()->flash('flashMessage', "{$clip->title} ".__FUNCTION__.' successfully');
    }

    private function setTimestampsToNearest5thMinute(Clip $clip): void
    {
        $time_availability_start_rounded = $clip->time_availability_start->roundMinute(5);
        $time_availability_end_rounded =
            ($clip->time_availability_end) ? $clip->time_availability_end->roundMinute(5) : null;

        $clip->time_availability_start = $time_availability_start_rounded;
        $clip->time_availability_end = $time_availability_end_rounded;
    }
}
