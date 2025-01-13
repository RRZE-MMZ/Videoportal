<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\Traits\Transferable;
use App\Models\Clip;
use App\Services\OpencastService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchOpencastAssets extends Command
{
    use Transferable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opencast:finished-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch opencast assets for empty clips';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(OpencastService $opencastService): int
    {
        // fetch all clips without video files
        Log::info('Fetching Opencast Assets Command: started');
        $emptyClips = Clip::doesntHave('assets')
            ->whereHas('series', function ($q) {
                $q->hasOpencastSeriesID();
            })
            ->limit(20)->get();
        /*
         * for each empty clip check if there are finished opencast events
         * and publish the video files
         */

        if ($counter = $emptyClips->count() > 0) {
            Log::info(
                "Fetching Opencast Assets Command: Found {$counter} clips! Searching Opencast API for events..."
            );
            $emptyClips->each(function ($clip) use ($opencastService) {
                // find finished workflows for every clip
                $events = $opencastService->getProcessedEventsBySeriesID($clip->series->opencast_series_id);

                $events->each(function ($event) use ($clip, $opencastService) {
                    if ($clip->opencast_event_id === $event['identifier']) {
                        $this->checkOpencastAssetsForUpload($clip, $event['identifier'], $opencastService);
                        $this->info("Videos from Clip {$clip->title} is online");
                    } else {
                        $this->info("No Opencast Event found for Clip {$clip->title} | [ID]:{$clip->id}");
                    }
                });
            });
            Log::info('Fetching Opencast Assets Command finished');

            return Command::SUCCESS;
        } else {
            Log::info('Fetching Opencast Assets Command: started');
            $this->info('No empty clips found');

            return Command::FAILURE;
        }
    }
}
