<?php

namespace App\Jobs;

use App\Models\Series;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MassDeleteClipsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Series $series, public array $clipIDs)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $affectedChapterIds = $this->series->clips()
            ->whereIn('id', $this->clipIDs)
            ->pluck('chapter_id')
            ->filter()
            ->unique();
        Log::info($affectedChapterIds);

        $unassignedClipDeleted = $this->series->clips()
            ->whereIn('id', $this->clipIDs)
            ->whereNull('chapter_id')
            ->exists();

        $this->series->clips()->whereIn('id', $this->clipIDs)->delete();

        // Update clips for each affected chapter
        foreach ($affectedChapterIds as $chapterId) {
            $chapter = $this->series->chapters()->find($chapterId);

            if ($chapter) {
                // Retrieve and sort the clips belonging to this chapter
                $clips = $chapter->clips()->orderBy('episode', 'asc')->get();

                // Update the episode numbers sequentially
                foreach ($clips as $index => $clip) {
                    $clip->update(['episode' => $index + 1]);
                }
            }
        }

        if ($unassignedClipDeleted) {
            $clipsWithoutChapter = $this->series->clips()
                ->whereNull('chapter_id')
                ->orderBy('episode', 'asc')
                ->get();

            foreach ($clipsWithoutChapter as $index => $clip) {
                $clip->update(['episode' => $index + 1]);
            }
        }
    }
}
