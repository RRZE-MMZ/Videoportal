<?php

namespace App\Listeners;

use App\Events\SeriesDeleted;

class DeleteSeriesAssets
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SeriesDeleted $event): void
    {
        // delete all documents
        $event->series->documents->each(function ($document) {
            $document->delete();
        });
    }
}
