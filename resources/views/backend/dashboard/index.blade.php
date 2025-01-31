@php use App\Models\Setting; @endphp
@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black pb-2 font-semibold text-3xl dark:text-white dark:border-white">
        {{ __('dashboard.welcome to personal dashboard', ['fullName' => auth()->user()->getFullNameAttribute() ]) }}
        !
    </div>
    @if($userSeriesCounter == 0)
        <div class="flex flex-col px-2 py-2 dark:text-white font-normal">
            <div>
                <p class="pt-2">
                    <span class="mr-2">{{ __('dashboard.start creating new series') }}</span>
                    <a href="{{route('series.create')}}">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('dashboard.new series') }}
                        </x-button>
                    </a>
                </p>
            </div>
            <div>
                <p class="mt-4 pt-2">
                    <span class="mr-2">{{ __('dashboard.start creating a new clip') }}</span>
                    <a href="{{route('clips.create')}}">
                        <x-button class="bg-blue-600 hover:bg-blue-700 font-normal">
                            {{ __('dashboard.new clip') }}
                        </x-button>
                    </a>
                </p>
            </div>
        </div>
    @endif
    <div class="flex">
        @php $dropBoxFilesCheck = count($files) > 0 && Setting::portal()->data['show_dropbox_files_in_dashboard'];  @endphp
        <div class="@if($dropBoxFilesCheck)) w-2/3 @else w-full @endif">
            @if($activeLivestreams->count()>0 && auth()->user()->can('administrate-assistant-pages'))
                @include('backend.dashboard._active-livestreams',['activeLivestreams' => $activeLivestreams])
            @endif
            @if($opencastEvents->isNotEmpty())
                @include('backend.dashboard._opencast-workflows',['opencastEvents' => $opencastEvents])
            @endif
        </div>
        @can('administrate-assistant-pages')
            @if($dropBoxFilesCheck)
                <div class="w-1/3 pl-4">
                    @include('backend.dashboard._dropzone-files')
                    @include('backend.dashboard._trending-clips')
                </div>
            @endif
        @endcan
    </div>
    @if($userSeriesCounter > 0)
        <div class="flex flex-col">
            <div>
                <div class="border-b border-black pt-5 pb-2 font-semibold font-2xl  dark:text-white dark:border-white">
                    {{ __('dashboard.your last series') }}
                </div>
            </div>
            <div>
                <livewire:index-pages-datatable :action-button="'dashboard'" />
            </div>

        </div>
    @endif
    @if($userClipsCounter > 0)
        <div class="flex flex-col">
            <div>
                <div class="border-b border-black pt-5 pb-2 font-semibold font-2xl  dark:text-white dark:border-white">
                    {{ __('dashboard.your last series') }}
                </div>
            </div>
            <div>
                <livewire:index-pages-datatable :type="'clips'" :action-button="'dashboard'" />
            </div>
        </div>
    @endif
@endsection
