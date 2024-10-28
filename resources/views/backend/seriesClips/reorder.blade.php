@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal pb-2">
        <div class="font-semibold ">
            {!! __('series.backend.reorder clips for series', ['series_title' => $series->title]) !!}
        </div>
    </div>
    @if ($series->chapters()->count() > 0)
        <div class="flex pt-10 flex-col ">
            <div class="flex h4 font-extrabold italic bg-yellow-200 rounded-2xl m-4 p-4 items-center">
                <div>
                    <x-iconoir-info-circle class="w-6 h-6" />
                </div>
                <div class="pl-4">
                    {{ __('series.backend.reorder series clips with chapters info') }}
                </div>
            </div>
            <div class="pt-10">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('series.chapters.index', $series) }}">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('series.backend.actions.manage chapters') }}
                        </x-button>
                    </a>
                    <a href="{{ route('series.edit', $series) }}">
                        <x-button class="bg-green-600 hover:bg-green-700">
                            {{ __('series.backend.actions.back to edit series') }}
                        </x-button>
                    </a>
                </div>

            </div>
        </div>
    @else
        <x-list-clips :series="$series" :clips="$clips" dashboardAction="@can('edit-series', $series)"
                      :reorder="true" />
    @endif

@endsection
