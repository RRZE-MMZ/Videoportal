<?php

namespace App\Jobs;

use App\Models\Clip;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MassDeleteClipsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $clipIDs)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        collect($this->clipIDs)->each(function (string $clipID) {
            $clip = Clip::find($clipID);
            $clip->delete();
        });
    }
}
