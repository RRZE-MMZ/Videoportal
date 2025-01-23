<div class="flex items-center py-4 space-x-4 mb-16  ">
    <a href="{{ route('series.clips.create', $series) }}">
        <x-button class="bg-blue-600 hover:bg-blue-700">
            {{ __('series.backend.actions.add new clip') }}
        </x-button>
    </a>
    <a href="{{ route('frontend.series.show', $series) }}">
        <x-button class="bg-blue-600 hover:bg-blue-700">
            {{ __('series.backend.actions.go to public page') }}
        </x-button>
    </a>
    <a href="{{ route('statistics.series', $series) }}">
        <x-button class="bg-blue-600 hover:bg-blue-700">
            {{ trans_choice('common.menu.statistic',2) }}
        </x-button>
    </a>
    @if($series->clips()->count()> 0 )
        <a href="{{ route('series.clips.batch.show.clips.metadata', $series) }}">
            <x-button class="bg-green-600 hover:bg-green-700">
                {{ __('series.backend.actions.edit metadata of multiple clips') }}
            </x-button>
        </a>
        @if($series->chapters()->count() == 0)
            <a href="{{ route('series.clips.changeEpisode', $series) }}">
                <x-button class="bg-green-600 hover:bg-green-700">
                    {{ __('series.backend.actions.reorder series clips') }}
                </x-button>
            </a>
        @endif
    @endif
    <a href="{{ route('series.chapters.index', $series) }}">
        <x-button class="bg-green-600 hover:bg-green-700">
            {{ __('series.backend.actions.manage chapters') }}
        </x-button>
    </a>
    @can('update-series', $series)
        <x-modals.delete :route="route('series.destroy', $series)">
            <x-slot:title>
                {{__('series.backend.delete.modal title',['series_title'=>$series->title])}}
            </x-slot:title>
            <x-slot:body>
                {{__('series.backend.delete.modal body')}}
            </x-slot:body>
        </x-modals.delete>
    @endcan
</div>
