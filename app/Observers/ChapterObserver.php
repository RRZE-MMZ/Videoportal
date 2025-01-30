<?php

namespace App\Observers;

use App\Models\Chapter;

class ChapterObserver
{
    /**
     * Handle the Chapter "created" event.
     */
    public function created(Chapter $chapter): void
    {
        session()->flash('flashMessage', __('common.metadata.chapter').": {$chapter->title} ".__FUNCTION__.' successfully');
        $chapter->series->recordActivity('Created chapter:'.$chapter->title);
    }

    public function updated(Chapter $chapter): void
    {
        $chapter->series->recordActivity('Updated chapter:'.$chapter->title, $chapter->activityChanges());
    }

    /**
     * Handle the Chapter "deleted" event.
     */
    public function deleted(Chapter $chapter): void
    {
        session()->flash('flashMessage', __('common.metadata.chapter').": {$chapter->title} ".__FUNCTION__.' successfully');
        $chapter->series->recordActivity('Deleted chapter:'.$chapter->title);
    }
}
