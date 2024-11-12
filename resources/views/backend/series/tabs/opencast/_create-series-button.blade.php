@can('administrate-assistant-pages')
    <div id="opencast-create-series-button" class="flex flex-col dark:text-white">
        <div class="font-normal">
            No opencast series found with this series ID
        </div>
        <div class="pt-5">
            <form action="{{route('series.opencast.createSeries', $series)}}"
                  method="POST"
            >
                @csrf
                <x-button class="bg-blue-600 hover:bg-blue-700">
                    Create Opencast series for this object
                </x-button>
            </form>
        </div>
    </div>
@endcan