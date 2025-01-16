<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Enums\OpencastWorkflowState;
use App\Models\Livestream;
use App\Models\Series;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LivestreamRoomEnabled;
use App\Services\OpencastService;
use App\Services\WowzaService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EnableLivestreams extends Command
{
    use Logable;

    protected $signature = 'app:enable-livestreams';

    protected $description = 'Check for planned opencast events and livestream clips to enable the livestream app';

    public function handle(OpencastService $opencastService, WowzaService $wowzaService)
    {
        $settings = Setting::portal();

        if ($opencastService->getHealth()->get('status') === 'failed') {
            $this->commandLog(message: 'No Opencast server found or server is offline!');

            return Command::SUCCESS;
        }

        $startDate = Carbon::now('UTC');
        $endDate = Carbon::now('UTC')->addMinutes(9);
        //        $endDate = (Carbon::now()->isDST()) ? Carbon::now()->addMinutes(110) : Carbon::now()->subMinutes(50);
        Log::info('Searching for active Opencast recording events without active livestream room reservation');
        $this->commandLog(message: 'Searching for active Opencast recording events');

        $recordingEvents = $opencastService->getEventsByStatus(state: OpencastWorkflowState::RECORDING);

        if ($recordingEvents->isEmpty()) {
            $this->commandLog(message: 'No active recording events found');
        } else {
            $counter = $recordingEvents->count();
            $eventsName = $recordingEvents->pluck('title');
            $this->commandLog(message: "Find $counter recording events: $eventsName");
        }
        $recordingEvents->each(function ($event) use ($wowzaService, $settings) {
            $series = Series::where('opencast_series_id', $event['is_part_of'])->first();
            $seriesLivestreamClip = $series->fetchLivestreamClip();

            if (! is_null($seriesLivestreamClip)
                &&
                is_null(Livestream::where('clip_id', $seriesLivestreamClip->id)->first())) {
                $this->commandLog(message: "Series '{$series->title}' has a livestream clip now try to enable"
                     ." wowza app {$event['scheduling']['agent_id']} for this clip"
                );
                $wowzaService->reserveLivestreamRoom(
                    opencastAgentID: $event['scheduling']['agent_id'],
                    livestreamClip: $seriesLivestreamClip,
                    endTime: $event['scheduling']['end']
                );
                if (app()->environment('production')) {
                    Notification::sendNow(User::admins()->get(), new LivestreamRoomEnabled($seriesLivestreamClip));

                } else {
                    $user = User::search($settings->data['admin_main_address'])->first();
                    Notification::sendNow($user, new LivestreamRoomEnabled($seriesLivestreamClip));
                }
            }
        });

        $msg = 'Check for Opencast scheduled events startDate:'.Carbon::now().' endDate:'.Carbon::now()->addMinutes(9);
        Log::info($msg);
        $this->commandLog(message: $msg);

        $events = $opencastService->getEventsByStatusAndByDate(
            state: OpencastWorkflowState::SCHEDULED,
            series: null,
            startDate: $startDate,
            endDate: $endDate
        );

        if ($events->isEmpty()) {
            $this->commandLog(message: 'No Opencast scheduled events found for the next 10 minutes');

            return Command::SUCCESS;
        }

        $events->each(function ($event) use ($wowzaService, $settings) {
            $series = Series::where('opencast_series_id', $event['is_part_of'])->first();
            $seriesLivestreamClip = $series->fetchLivestreamClip();

            if ($seriesLivestreamClip &&
                is_null(Livestream::where('clip_id', $seriesLivestreamClip->id)->first())
            ) {
                $this->commandLog(message: "Series '{$series->title}' has a livestream clip now try to enable"
                     ." wowza app {$event['scheduling']['agent_id']} for this clip"
                );
                $wowzaService->reserveLivestreamRoom(
                    opencastAgentID: $event['scheduling']['agent_id'],
                    livestreamClip: $seriesLivestreamClip
                );
                if (app()->environment('production')) {
                    Notification::sendNow(User::admins()->get(), new LivestreamRoomEnabled($seriesLivestreamClip));
                } else {
                    $user = User::search($settings->data['admin_main_address'])->first();
                    Notification::sendNow($user, new LivestreamRoomEnabled($seriesLivestreamClip));
                }
            }
        });
    }
}
