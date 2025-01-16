<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Http\Controllers\Backend\Traits\Transferable;
use App\Models\Clip;
use App\Services\OpencastService;
use Illuminate\Console\Command;

class FetchOpencastAssets extends Command
{
    use Logable;
    use Transferable;

    protected $signature = 'opencast:finished-events';

    protected $description = 'Fetch opencast assets for empty clips';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(OpencastService $opencastService): int
    {
        // fetch all clips without video files
        $this->commandLog(message: 'Fetching Opencast Assets Command: started');
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
            $this->commandLog(message: "Fetching Opencast Assets Command: Found {$counter} clips! Searching Opencast API for events..."
            );
            $emptyClips->each(function ($clip) use ($opencastService) {
                // find finished workflows for every clip
                $events = $opencastService->getProcessedEventsBySeriesID($clip->series->opencast_series_id);

                $events->each(function ($event) use ($clip, $opencastService) {
                    if ($clip->opencast_event_id === $event['identifier']) {
                        $this->checkOpencastAssetsForUpload($clip, $event['identifier'], $opencastService);
                        $this->commandLog(message: "Videos from Clip {$clip->title} is online");
                    } else {
                        $this->commandLog(message: "No Opencast Event found for Clip {$clip->title} | [ID]:{$clip->id}");
                    }
                });
            });
            $this->commandLog(message: 'Fetching Opencast Assets Command finished');

            return Command::SUCCESS;
        } else {
            $this->commandLog(message: 'No empty clips found');

            return Command::FAILURE;
        }
    }
}
