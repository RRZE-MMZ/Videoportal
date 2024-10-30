@use('App\Enums\Content')
@use ('App\Models\Setting')
@extends('layouts.frontend')

@section('content')
    <div class="w-full md:container py-10 mx-auto">
        @include('frontend.search._searchbar')
    </div>
    @include('layouts.breadcrumbs')
    <main class="container mx-auto mt-6 md:mt-12">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Podcast Information -->
            <div class="p-6 rounded-lg  h-200 dark:text-white">
                <div class="mb-4">
                    <img
                            @if(!is_null($episode->image_id))
                                src="{{ asset('images/'.$episode->cover->file_name) }}" alt="Podcast Cover 1"
                            @elseif(!is_null($episode->podcast->image_id))
                                src="{{ asset('images/'.$episode->podcast->cover->file_name) }}" alt="Podcast Cover 2"
                            @else
                                src="/podcast-files/covers/PodcastDefaultFAU.png" alt="Podcast Cover 3"
                            @endif
                            alt="Podcast Cover"
                            class="w-full h-auto rounded-md">
                </div>
                @if($episode->getAssetsByType(Content::AUDIO)->first())
                    <div class=" space-y-4 ">
                        <div class="mt-4 ">
                            <audio id="player" class="w-full" controls>
                                <source
                                        src="{{ getProtectedUrl($episode->getAssetsByType(Content::AUDIO)->first()->path) }}"
                                        type="audio/mp3" />
                            </audio>
                        </div>
                        <!-- Add more episodes as needed -->
                    </div>
                @endif
            </div>

            <!-- Podcast Episodes -->
            <div class=" flex flex-col p-6 rounded-lg space-y-4">
                <h1 class="text-2xl font-bold mb-2 dark:text-white">
                    {{$episode->episode_number. ' - '. $episode->title}}   @can('administrate-superadmin-portal-pages')
                        {{ ' / EpisodeID - '.$episode->id }}
                    @endcan
                </h1>
                <div class="prose prose-lg dark:prose-invert dark:text-white">
                    <p class=" mb-4">
                        {!!  $episode->description  !!}
                    </p>
                </div>

                @if($episode->presenters()->first())
                    <div>
                        <span class="text-gray-600 dark:text-white">
                        {{ __('podcastEpisode.frontend.hosted by') }}
                    </span>
                        <span class="text-gray-800 font-semibold dark:text-white">
                        {{ $episode->presenters->first()->getFullNameAttribute() }}
                    </span>
                    </div>
                @endif
                @if($episode->presenters()->first())
                    <div>
                    <span class="text-gray-600 dark:text-white">
                        {{ trans_choice('common.guest', 2) }}:
                    </span>
                        <span class="text-gray-800 font-semibold dark:text-white">
                            Max Mustermann, John Doe
                    </span>
                    </div>
                @endif
                <div class="mt-4">
                    <span class="text-gray-600 dark:text-white">
                        {{ trans_choice('common.categories', 2) }}:</span>
                    <span
                            class="inline-block bg-blue-200 text-blue-800 text-sm px-2 py-1 rounded-full">Podcast</span>
                </div>
            </div>
        </div>

        @if($episode->notes)
            <div class="flex pb-4">
                <h3 class="text-2xl font-semibold">
                    {{ __('podcastEpisode.frontend.show notes') }}
                </h3>
            </div>
            <article>
                {{$episode->notes}}
            </article>

        @endif
        @if($episode->transcription)
            <div class="">
                <div class="flex pb-4 dark:text-white">
                    <h3 class="text-2xl font-semibold">
                        {{__('podcastEpisode.frontend.show transcript')}}
                    </h3>
                </div>
                <div class="w-full">
                    <div class="w-full prose-lg dark:prose-invert">
                        <span class="w-full ">
                            <div class="prose-lg dark:prose-invert dark:text-white">
                                {!! $episode->transcription !!}
                            </div>
                        </span>
                    </div>
                </div>
            </div>

        @endif

    </main>
@endsection
