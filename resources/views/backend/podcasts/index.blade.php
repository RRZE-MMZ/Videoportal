@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            {{ __('podcast.common.podcast index') }}
        </div>
        <div class="flex">
            <a href="{{route('podcasts.create')}}">
                <x-button class="flex items-center bg-blue-600 hover:bg-blue-700">
                    <div class="pr-2">
                        {{ __('podcast.backend.actions.create new podcast') }}
                    </div>
                    <div>
                        <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                             xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                             viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                  d="M12 5a7 7 0 0 0-7 7v1.17c.313-.11.65-.17 1-.17h2a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H6a3 3 0 0 1-3-3v-6a9 9 0 0 1 18 0v6a3 3 0 0 1-3 3h-2a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h2c.35 0 .687.06 1 .17V12a7 7 0 0 0-7-7Z"
                                  clip-rule="evenodd" />
                        </svg>
                    </div>
                </x-button>
            </a>
        </div>
    </div>
    {{--    <livewire:series-data-table />--}}
    <div class="grid gap-8 mb-6 lg:mb-16 md:grid-cols-2 grid-cols-2">
        @forelse($podcasts as $podcast)
            @include('partials.podcasts._card', $podcast)
        @empty
            <p class="col-span-4 text-3xl dark:text-white italic items-center">
                {{ __('podcast.frontend.no podcasts available or published') }}
            </p>
        @endforelse
    </div>
@endsection

