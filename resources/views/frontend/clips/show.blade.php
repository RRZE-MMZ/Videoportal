@php use App\Models\Setting; @endphp
@extends('layouts.frontend')

@section('content')
    <main class="container mx-auto px-4 mt-4 sm:mt-6 md:mt-12">
        @if($playerSetting->data['player_show_article_link_in_player'])
            @include('frontend.clips._article_section')
        @endif
        <div class="flex flex-col sm:flex-row items-center border-b-2 border-black pb-2 dark:border-white">
            <div class="flex-grow mb-2 sm:mb-0">
                <h2 id="clip-title"
                    class="text-xl sm:text-2xl font-bold dark:text-white text-center sm:text-left"
                >
                    @if($clip->series_id)
                        {{ $clip->episode }} -
                    @endif {{ $clip->title }} [ID: {{ $clip->id }}]
                </h2>
            </div>
            @can('edit-clips', $clip)
                <div class="flex-none">
                    <a href="{{ route('clips.edit', $clip)}}">
                        <x-button class="bg-blue-500 hover:bg-blue-600 text-sm sm:text-base">
                            {{ __('clip.frontend.show.Back to clip edit page') }}
                        </x-button>
                    </a>
                </div>
            @endcan
        </div>

        <div class="flex flex-col align-center mt-4">
            @if (!is_null($clip->assets()->first()) || $clip->is_livestream)
                @include('frontend.clips._player',['asset'=> $clip->assets()])
            @elseif(auth()->user()?->can('edit-clips', $clip))
                @include('frontend.clips._info')
            @endif
        </div>

        <div class="flex flex-col sm:flex-row justify-between pt-20">
            @if(!is_null($previousNextClipCollection->get('previousClip')))
                <a class="flex w-full sm:w-auto flex-row items-center justify-center sm:justify-start mb-2 sm:mb-0"
                   href="{{ route('frontend.clips.show',$previousNextClipCollection->get('previousClip')) }}">
                    <x-button class="bg-blue-600 hover:bg-blue-700 text-xs sm:text-sm">
                        <div class="mr-2 sm:mr-4">
                            <x-heroicon-o-arrow-left class="w-4 sm:w-6" />
                        </div>
                        <div>
                            {{ __('common.previous') . ' - ' . $previousNextClipCollection->get('previousClip')->title }}
                        </div>
                    </x-button>
                </a>
            @endif
            @if(!is_null($previousNextClipCollection->get('nextClip')))
                <a class="flex w-full sm:w-auto flex-row items-center justify-center sm:justify-end"
                   href="{{ route('frontend.clips.show', $previousNextClipCollection->get('nextClip')) }}">
                    <x-button class="bg-blue-600 hover:bg-blue-700 text-xs sm:text-sm">
                        <div>
                            {{ __('common.next') . ' - ' . $previousNextClipCollection->get('nextClip')->title }}
                        </div>
                        <div class="ml-2 sm:ml-4">
                            <x-heroicon-o-arrow-right class="w-4 sm:w-6" />
                        </div>
                    </x-button>
                </a>
            @endif
        </div>

        @if($clip->description !== null && $clip->description !=='')
            <h2 class="py-2 text-lg sm:text-2xl font-semibold dark:text-white">{{ __('common.description') }}</h2>
            <div class="w-full">
                <div class="dark:prose-invert dark:text-white text-sm sm:text-base">
                    <p>
                        {!! $clip->description  !!}
                    </p>
                </div>
            </div>
        @endif

        @if ($clip->tags->isNotEmpty())
            <div class="flex flex-col pt-6 sm:pt-10">
                <h2 class="w-full border-b-2 border-black pb-2 dark:border-white text-lg sm:text-2xl
                font-semibold dark:text-white"
                >
                    Tags
                </h2>
                <div class="flex flex-wrap pt-4 gap-2">
                    @foreach($clip->tags as $tag)
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
        @can('view-clips-comments', $clip)
            <div class="flex flex-col pt-6 sm:pt-10">
                <h2 class="border-b-2 border-black pb-2 text-lg sm:text-2xl font-semibold dark:text-white dark:border-white">
                    {{ __('clip.frontend.comments') }}
                </h2>
                <livewire:comments-section :model="$clip" :type="'frontend'" />
            </div>
        @endauth
    </main>
@endsection
