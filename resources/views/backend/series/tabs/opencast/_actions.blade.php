@can('administrate-assistant-pages')
    <div id="opencast-series-actions" class="flex flex-col font-normal py-4">
        <h4 class="mt-4 mb-4 text-green-700  dark:text-green-400">
            <span class="text-xl font-bold">
                {{ __('opencast.backend.actions.Opencast actions') }}
            </span>
        </h4>
        <div class="flex space-x-4">
            <form action="{{route('series.opencast.updateEventsTitle', $series)}}"
                  method="POST"
                  class="flex"
            >
                @csrf
                <input hidden readonly type="text" name="opencastSeriesID" value="{{$series->opencast_series_id}}">
                <x-button type="link" class="bg-green-700 hover:bg-green-800">
                    {{ __('opencast.backend.actions.remove numbers from title') }}
                </x-button>
            </form>
            <form action="{{ route('series.opencast.addScheduledEventsAsClips', $series) }}"
                  method="POST"
                  class="flex"
            >
                @csrf
                <input hidden readonly type="text" name="opencastSeriesID" value="{{$series->opencast_series_id}}">
                <x-button class="bg-green-700 hover:bg-green-800">
                    {{ __('opencast.backend.actions.add all planned recordings as clips') }}
                </x-button>
            </form>

        </div>
    </div>
@endcan
