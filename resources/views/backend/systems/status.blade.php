@extends('layouts.backend')

@section('content')

    <div class="flex items-center border-b border-black pb-2 font-semibold font-2xl align-items-center
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            Systems check
        </div>
    </div>

    <div class="grid gap-4 pt-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 font-normal
            dark:text-white">
        <div class="m-2 rounded-lg border-2 border-solid border-black dark:border-white p-2">
            <div class="flex place-content-around justify-between">
                <div>
                    <h3 class="pb-6 font-semibold text-fuchsia-800">Portal
                    </h3>
                </div>
                <div>
                    <x-heroicon-o-check-circle class="h-6 w-6 rounded-sm text-green-600" />
                </div>
            </div>

            <div>
                <div>
                    Tides Portal version:<span class="font-bold"> 0.1 </span>
                </div>
                <div>
                    Description : just another videoportal </span>
                </div>
            </div>
            <div class="pt-5">
                <a href="{{ route('settings.portal.show') }}" class="flex flex-row">
                    <x-button type="button"
                              class="flex basis-1/2 content-center justify-center justify-between bg-blue-600 hover:bg-blue-700">
                        <div>
                            Settings
                        </div>
                        <div>
                            <x-heroicon-o-arrow-right class="w-6" />
                        </div>
                    </x-button>
                </a>
            </div>
        </div>
        <div class="m-2 rounded-lg border-2 border-solid border-black  dark:border-white p-2 p-2">
            <div class="flex place-content-around justify-between">
                <div>
                    <h3 class="pb-6 font-semibold text-green-500"> Video workflow
                    </h3>
                </div>
                <div>
                    @if($opencastStatus->contains('pass'))
                        <x-heroicon-o-check-circle class="h-6 w-6 rounded-sm text-green-600" />
                    @else
                        <x-heroicon-o-x-circle class="h-6 w-6 rounded-sm text-red-600" />
                    @endif
                </div>
            </div>

            <div>
                <div>
                    Opencast version:<span class="font-bold"> {{$opencastStatus['releaseId']}} </span>
                </div>
                <div>
                    Description : <span class="font-bold"> {{$opencastStatus['description']}} </span>
                </div>
            </div>
            <div class="pt-5">
                <a href="{{ route('settings.workflow.show') }}" class="flex flex-row">
                    <x-button type="button"
                              class="flex basis-1/2 content-center justify-center justify-between bg-blue-600 hover:bg-blue-700">
                        <div>
                            Settings
                        </div>
                        <div>
                            <x-heroicon-o-arrow-right class="w-6" />
                        </div>
                    </x-button>
                </a>
            </div>
        </div>
        <div class="m-2 rounded-lg border-2 border-solid border-black  dark:border-white p-2 p-2">
            <div class="flex place-content-around justify-between">
                <div>
                    <h3 class="pb-6 font-semibold text-orange-500">Video Streaming
                    </h3>
                </div>
                <div>
                    @if($wowzaStatus->contains('pass'))
                        <x-heroicon-o-check-circle class="h-6 w-6 rounded-sm text-green-600" />
                    @else
                        <x-heroicon-o-x-circle class="h-6 w-6 rounded-sm text-red-600" />
                    @endif
                </div>
            </div>

            <div>
                <div>
                    Wowza description:<span class="font-bold"> {{str($wowzaStatus['releaseId'])->remove('"')}} </span>
                </div>
            </div>
            <div class="pt-5">
                <a href="{{ route('settings.streaming.show') }}" class="flex flex-row">
                    <x-button type="button"
                              class="flex basis-1/2 content-center justify-center justify-between bg-blue-600 hover:bg-blue-700">
                        <div>
                            Settings
                        </div>
                        <div>
                            <x-heroicon-o-arrow-right class="w-6" />
                        </div>
                    </x-button>
                </a>
            </div>
        </div>
        <div class="m-2 rounded-lg border-2 border-solid border-black  dark:border-white p-2 p-2">
            <div class="flex place-content-around justify-between">
                <div>
                    <h3 class="pb-6 font-semibold  text-blue-500">Search
                    </h3>
                </div>
                <div>
                    @if($openSearchStatus->contains('pass'))
                        <x-heroicon-o-check-circle class="h-6 w-6 rounded-sm text-green-600" />
                    @else
                        <x-heroicon-o-x-circle class="h-6 w-6 rounded-sm text-red-600" />
                    @endif
                </div>
            </div>

            @if($openSearchStatus->contains('pass'))
                <div>
                    <div>
                        Version:<span
                            class="font-bold"> {{ $openSearchStatus->get('releaseId')['version']['number'] }} </span>
                    </div>
                    <div>
                        Description:<span
                            class="font-bold">{{ $openSearchStatus->get('releaseId')['version']['distribution'] }}
                    </span>
                    </div>
                </div>
            @else
                <div>
                    <div>
                        OpenSearch server does not exists or not configured successfully </span>
                    </div>
                </div>
            @endif
            <div class="pt-5">
                <a href="{{ route('settings.openSearch.show') }}" class="flex flex-row">
                    <x-button type="button"
                              class="flex basis-1/2 content-center justify-center justify-between bg-blue-600 hover:bg-blue-700">
                        <div>
                            Settings
                        </div>
                        <div>
                            <x-heroicon-o-arrow-right class="w-6" />
                        </div>
                    </x-button>
                </a>
            </div>
        </div>
    </div>

@endsection
