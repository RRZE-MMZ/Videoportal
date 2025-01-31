@use(App\Enums\Acl)
@extends('layouts.frontend')

@section('content')
    <div class="w-full md:container py-10 mx-auto">
        @include('frontend.search._searchbar')
    </div>
    <div class="container mx-auto mt-4 md:mt-4 dark:text-white">
        <div class="flex flex-col md:flex-row sm:flex-row justify-between border-b-2 border-black pb-2 dark:border-white items-center">
            <div>
                <h2 class="text-2xl font-bold">{{ $series->title }} [ID: {{ $series->id }}]</h2>
            </div>
            @cannot('administrate-admin-portal-pages')
                @if(str()->contains($series->fetchClipsAcls(), [Acl::PASSWORD->lower()]))
                    <div>
                        <livewire:unlock-object :model="$series" />
                    </div>
                @endif
            @endcannot
            @can('edit-series', $series)
                <div class="">
                    <a href="{{ route('series.edit', $series) }}">
                        <x-button class="bg-blue-500 hover:bg-blue-700">
                            {{__('series.common.edit series')}}
                        </x-button>
                    </a>
                </div>
            @endcan
        </div>
        @if($series->description !== '')
            <div class="w-full  dark:prose-invert dark:text-white mt-4 sm:mt-6">
                <p class="break-words">
                    {!! $series->description !!}
                </p>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row mt-4">
            <div x-data="{ open: false }" class="w-full sm:w-auto">
                <div class="flex w-full sm:w-auto pt-4 pr-4">
                    <a href="#courseFeeds"
                       x-on:click="open = !open"
                       class="flex px-4 py-2 bg-blue-800 border border-transparent rounded-md
                            font-semibold text-xs text-white uppercase tracking-widest
                            hover:bg-blue-700 active:bg-blue-900 focus:outline-hidden focus:border-blue-900
                            focus:ring-3 ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Feeds
                        <x-heroicon-o-rss class="ml-4 h-4 w-4 fill-white" />
                    </a>
                </div>
                @if ($series->tags->isNotEmpty())
                    <div class="flex flex-col pt-6 sm:pt-10">
                        <div class="flex flex-wrap pt-4 gap-2">
                            @foreach($series->tags as $tag)
                                <div
                                        class="text-xs sm:text-sm mr-1 inline-flex items-center font-bold leading-sm px-2 sm:px-3 py-1 bg-green-200
                            text-green-700 rounded-full"
                                >
                                    {{ $tag->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-0"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 translate-y-10"
                     x-transition:leave-end="opacity-0 translate-y-0" class="w-full p-4">
                    <ul>
                        <li>
                            <a href="{{ route('frontend.series.feed', [$series, 'QHD']) }}"
                               class="underline">
                                QHD
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('frontend.series.feed', [$series, 'HD']) }}"
                               class="underline">
                                HD
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('frontend.series.feed', [$series, 'SD']) }}"
                               class="underline">
                                SD
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('frontend.series.feed', [$series, 'AUDIO']) }}"
                               class="underline">
                                Audio
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            @auth()
                <div class="mt-4 sm:mt-0 sm:ml-4 w-full sm:w-auto">
                    <livewire:subscribe-section :series="$series" />
                </div>
            @endauth
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 border-b-2 border-gray-500 py-4 my-4 gap-4">
            <div class="flex items-center align-middle">
                <x-iconoir-calendar class="h-6 w-6 shrink-0" />
                <span class="pl-3">
            {{ $series->fetchClipsSemester() }}
        </span>
            </div>

            @if($series->presenters->isNotEmpty())
                <div class="flex items-center align-middle">
                    <x-iconoir-user-badge-check class="h-6 w-6 shrink-0" />
                    <span class="pl-3">
                {{ $series->presenters->map(function ($presenter) {
                    return $presenter->getFullNameAttribute();
                })->implode(', ') }}
            </span>
                </div>
            @endif

            <div class="flex items-center align-middle">
                <x-iconoir-card-lock class="h-6 w-6 shrink-0" />
                <span class="pl-3">
                {{ $series->fetchClipsAcls() }}
                </span>
            </div>

            <div class="flex items-center align-middle">
                <x-iconoir-upload-square class="h-6 w-6 shrink-0" />
                <span class="pl-3"> {{ $series->latestClip?->updated_at }} </span>
            </div>

            <div class="flex items-center align-middle">
                <x-iconoir-database-stats class="h-6 w-6 shrink-0" />
                <span class="pl-3"> {{ __('series.frontend.show.views', ['counter' => $series->views()]) }} </span>
            </div>
        </div>


        @can('view-series-comments', $series)
            <div class="flex flex-col pt-10">
                <h2 class="border-b-2 border-black dark:border-white pb-2 text-2xl font-semibold">
                    {{ __('clip.frontend.comments') }}
                </h2>
                <livewire:comments-section :model="$series" :type="'frontend'" />
            </div>
        @endcan

        @include('backend.clips.list')
    </div>
@endsection
