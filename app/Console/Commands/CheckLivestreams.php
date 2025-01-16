<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Models\Livestream;
use App\Services\OpencastService;
use Illuminate\Console\Command;

class CheckLivestreams extends Command
{
    use Logable;

    protected $signature = 'app:check-livestreams';

    protected $description = 'Check for active livestreams and disables them';

    public function handle(OpencastService $opencastService)
    {
        if ($opencastService->getHealth()->get('status') === 'failed') {
            $this->commandLog(message: 'No Opencast server found or server is offline!');

            return Command::SUCCESS;
        }

        $activeLivestreams = Livestream::active()->get();
        if ($activeLivestreams->isEmpty()) {
            $this->commandLog(message: 'No active livestreams found');

            return Command::SUCCESS;
        }

        $activeLivestreams->each(function ($livestream) {
            // TODO insert livestream stats from wowza api and update (?) the app names
            if ($livestream->time_availability_end->isPast()) {
                $livestream->clip_id = null;
                $livestream->active = false;
                $livestream->save();
                $this->commandLog(message: "Disable livestream {$livestream->name}.");
            } else {
                $this->info("Livestream {$livestream->name} is still active.");
            }
        });
    }
}
