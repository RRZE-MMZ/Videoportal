<?php

namespace App\Observers;

use App\Events\SeriesTitleUpdated;
use App\Http\Resources\SeriesResource;
use App\Models\Series;
use App\Services\OpenSearchService;

class SeriesObserver
{
    public function __construct(
        readonly private OpenSearchService $openSearchService,
    ) {}

    /**
     * Handle the Series "created" event.
     */
    public function created(Series $series): void
    {
        session()->flash('flashMessage', "{$series->title} ".__FUNCTION__.' successfully');

        $this->openSearchService->createIndex(new SeriesResource($series));
    }

    public function updating(Series $series): void
    {
        if ($series->isDirty('title')) {
            SeriesTitleUpdated::dispatch($series);
        }
    }

    /**
     * Handle the Series "updated" event.
     */
    public function updated(Series $series): void
    {
        session()->flash('flashMessage', "{$series->title} ".__FUNCTION__.' successfully');

        $this->openSearchService->updateIndex(new SeriesResource($series));
    }

    /**
     * Handle the Series "deleted" event.
     */
    public function deleted(Series $series): void
    {
        session()->flash('flashMessage', "{$series->title} ".__FUNCTION__.' successfully');

        $this->openSearchService->deleteIndex($series);
    }

    /**
     * Handle the Series "restored" event.
     */
    public function restored(Series $series): void
    {
        session()->flash('flashMessage', "{$series->title} ".__FUNCTION__.' successfully');
    }

    /**
     * Handle the Series "force deleted" event.
     */
    public function forceDeleted(Series $series): void
    {
        session()->flash('flashMessage', "{$series->title} ".__FUNCTION__.' successfully');
    }
}
