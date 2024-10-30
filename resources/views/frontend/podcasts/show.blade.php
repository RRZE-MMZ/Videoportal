@php use App\Models\Setting; use Illuminate\Support\Str; @endphp
@extends('layouts.frontend')

@section('content')
    <div class="w-full md:container py-10 mx-auto">
        @include('frontend.search._searchbar')
    </div>
    <main class="container mx-auto mt-6 sm:mt-8 md:mt-12 px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-center pb-4">
            <div class="w-full sm:w-auto mb-4 sm:mb-0">
                @include('layouts.breadcrumbs')
            </div>
            @can('edit-podcast', $podcast)
                <div class="w-full sm:w-auto">
                    <a href="{{ route('podcasts.edit', $podcast) }}">
                        <x-button class="bg-green-500 hover:bg-green-700 w-full sm:w-auto text-sm sm:text-base">
                            {{ __('podcast.common.edit podcast') }}
                        </x-button>
                    </a>
                </div>
            @endcan
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Podcast Information -->
            <div class="p-4 sm:p-6 rounded-lg dark:text-white bg-white dark:bg-gray-800 shadow-md">
                <div class="mb-4">
                    <img
                            @if(!is_null($podcast->image_id))
                                src="{{ asset('images/'.$podcast->cover->file_name) }}"
                            @else
                                src="/podcast-files/covers/PodcastDefaultFAU.png"
                            @endif
                            alt="{{ $podcast->title }} cover image"
                            class="w-full h-auto rounded-md">
                </div>
                <h1 class="text-xl sm:text-2xl font-bold mb-2">
                    {{ $podcast->title }} @can('administrate-superadmin-portal-pages')
                        / ID: {{ $podcast->id }}
                    @endcan
                </h1>
                <div class="prose prose-sm sm:prose-lg dark:prose-invert">
                    <p class="text-black dark:text-white">
                        {!! $podcast->description !!}
                    </p>
                </div>
                <div class="dark:text-white mt-4">
                    @if($podcast->getPrimaryPresenters()->count() > 0)
                        <span class="text-gray-600 dark:text-white">
                            {{ __('podcastEpisode.frontend.hosted by') }}
                        </span>
                        <span class="text-gray-800 font-semibold dark:text-white">
                            {{ $podcast->getPrimaryPresenters()->map(fn($presenter) => $presenter->full_name)->join(', ') }}
                        </span>
                    @endif
                </div>
                <div class="dark:text-white mt-2">
                    @if($podcast->getPrimaryPresenters(primary: false)->count() > 0)
                        <span class="text-gray-600 dark:text-white">{{ trans_choice('common.guest', 2) }}:</span>
                        <span class="text-gray-800 font-semibold dark:text-white">
                            {{ $podcast->getPrimaryPresenters(primary: false)->map(fn($presenter) => $presenter->full_name)->join(', ') }}
                        </span>
                    @endif
                </div>
                <div class="mt-4">
                    <span class="text-gray-600 dark:text-white">{{ trans_choice('common.categories',2) }}:</span>
                    <span class="inline-block bg-blue-200 text-blue-800 text-xs sm:text-sm px-2 py-1 rounded-full">
                        Podcast
                    </span>
                </div>
            </div>

            <!-- Podcast Episodes -->
            <div class="p-4 sm:p-6 rounded-lg">
                <div class="space-y-4">
                    @foreach($podcast->episodes()->orderBy('episode_number')->get() as $episode)
                        <div class="p-4 border rounded-lg bg-white shadow-md dark:bg-gray-900">
                            <h3 class="text-lg sm:text-xl font-semibold mb-2 dark:text-white">
                                {{ $episode->episode_number . ' - ' . $episode->title }}
                            </h3>
                            <p class="text-sm sm:text-base text-black dark:text-white mb-2">
                                @if($episode->description === '')
                                    {!! Str::limit('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab, assumenda atque beatae deserunt dolorem ducimus enim error illo incidunt odio pariatur, possimus quaerat quasi quidem quos temporibus unde vero. Quo?', 120, ' (...)') !!}
                                @else
                                    {{ Str::limit(removeHtmlElements($episode->description), 250, ' (...)') }}
                                @endif
                            </p>
                            <div class="pt-4">
                                <a href="{{ route('frontend.podcasts.episode.show', [$podcast, $episode]) }}"
                                   class="text-blue-500 hover:underline">
                                    {{ __('podcast.frontend.episode details') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
@endsection
