<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Livestream;
use App\Models\Series;
use App\Services\OpencastService;
use App\Services\WowzaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ManageLivestreamRoom extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function reserve(Request $request, OpencastService $opencastService, WowzaService $wowzaService)
    {
        $this->authorize('administrate-assistant-pages');
        // Get all inputs
        $inputs = $request->all();

        // Initialize the validation rules array
        $rules = [];

        // Iterate over the inputs and add rules for each location field
        foreach ($inputs as $key => $value) {
            if (str_starts_with($key, 'event_')) {
                $rules[$key] = 'sometimes|uuid';
            }
        }

        $rules['clipID'] = 'sometimes|exists:clips,id';
        $rules['livestreamID'] = 'sometimes|exists:livestreams,id';

        // Validate the request
        $validator = Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $validated = collect($validator->validated());

        $livestreamClip = null;

        $containsEventID = $validated->keys()->contains(function ($key) {
            return str_starts_with($key, 'event_');
        });
        //reserve the livestream based on an opencast event
        if ($containsEventID) {
            $eventID = $validated->first();
            $event = $opencastService->getEventByEventID($eventID);
            //now check if a livestream clip for this series exists
            $series = Series::where('opencast_series_id', $event['is_part_of'])->first();

            if (! is_null($series)) {
                $livestreamClip = $series->fetchLivestreamClip();
            }
            if (is_null($wowzaService->reserveLivestreamRoom(
                opencastAgentID: $event['scheduling']['agent_id'],
                livestreamClip: $livestreamClip,
                endTime: $event['scheduling']['end']
            ))) {
                session()->flash(
                    'errorMessage',
                    'No livestream room found for Opencast location:'.$event['scheduling']['agent_id']
                );
            }
        } else {
            $livestreamClip = Clip::find($validated->get('clipID'));
            $livestream = Livestream::find($validated->get('livestreamID'));
            if ($livestream->active) {
                session()->flash(
                    'errorMessage',
                    'The room is currently in use by another clip, so the reservation cannot be made.'
                );
            }
            $wowzaService->reserveLivestreamRoom(
                livestreamClip: $livestreamClip,
                livestreamRoomName: $livestream->name
            );
        }

        return redirect()->back();
    }

    public function cancel(Livestream $livestream)
    {
        $livestream->clip->recordActivity('Disabled livestream room - '.$livestream->name);
        $livestream->clip_id = null;
        $livestream->time_availability_end = Carbon::now(); // needs to be calculated properly
        $livestream->active = false;
        $livestream->save();
        $livestream->recordActivity('Disabled livestream room - '.$livestream->name);

        return redirect()->back();
    }
}
