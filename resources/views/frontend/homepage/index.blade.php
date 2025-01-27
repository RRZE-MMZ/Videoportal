@extends('layouts.frontend')

@section('content')

    <section class="bg-white dark:bg-slate-600">
        <div class="py-8 px-4 mx-auto max-w-(--breakpoint-xl) text-center lg:py-16">
            <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-5xl
                lg:text-6xl dark:text-white">
                {{ __('homepage.jumbotron heading') }}
            </h1>
            <p class="mb-8 text-lg font-normal text-gray-500 lg:text-xl sm:px-16 lg:px-48 dark:text-gray-200">
                {{ __('homepage.jumbotron subheading') }}
            </p>
            <div class="flex flex-col space-y-4 sm:flex-row sm:justify-center sm:space-y-0 space-x-0 sm:space-x-2">
                <a href="{{route('frontend.series.index')}}"
                   class="inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-white
                    rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                    {{ __('homepage.jumbotron link 1') }}
                    <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </a>
                <a href="https://github.com/rrze-mmz/Tides"
                   class="inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-white
                    rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                    <div class="flex justify-center items-center space-x-2">
                        <div>
                            {{__('homepage.jumbotron link 2')}}
                        </div>
                        <div>
                            <x-iconoir-git class="w-5 h-5" />
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <main class="container sm:mx-auto sm:mt-16">
        @include('frontend.search._searchbar')
        @auth
            @if(auth()->user()->settings->data['show_subscriptions_to_home_page'])
                <div class="w-full border-b-2 pb-4 border-black dark:border-white">
                    <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end pb-2">
                        <div class="text-2xl dark:text-white font-bold">
                            {{ __('homepage.series.Your series subscriptions') }}
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="{{ route('frontend.series.index') }}"
                               class="inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white
                                      rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                                {{__('homepage.series.more series') }}
                                <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="grid xl:grid-cols-4 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-4 pt-8">
                    @forelse(auth()->user()->subscriptions as $single)
                        @include('backend.series._card',[
                                'series'=> $single,
                                'route' => 'admin'
                                ])
                    @empty
                        <div class="dark:text-white text-2xl">
                            {{ __('homepage.series.You are not subscribed to any series') }}
                        </div>
                    @endforelse
                </div>
            @endif

            @if($portalSeries->isNotEmpty())
                <div class="w-full border-b-2 pb-4 border-black dark:border-white">
                    <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end pb-2">
                        <div class="text-2xl dark:text-white font-bold">
                            {{  __('homepage.series.Recently added!') }} Portal
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a href="{{ route('frontend.series.index') }}"
                               class="inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white
                                      rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                                {{__('homepage.series.more series') }}
                                <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg"
                                     fill="none" viewBox="0 0 14 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid xl:grid-cols-4 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-4 pt-8">
                    @forelse($portalSeries as $single)
                        @include('backend.series._card',[
                                'series'=> $single,
                                'route' => 'admin'
                                ])
                    @empty
                        <div class="dark:text-white text-2xl pt-10">
                            {{ __('homepage.series.no series found' )}}
                        </div>
                    @endforelse
                </div>
            @endif
        @endauth

        <div class="w-full border-b-2 pb-4 pt-10 border-black dark:border-white">
            <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end pb-2">
                <div class="text-2xl dark:text-white font-bold">{{ __('homepage.series.Recently added!') }}</div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('frontend.series.index') }}"
                       class="inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white
                              rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                        {{__('homepage.series.more series') }}
                        <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid xl:grid-cols-4 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-4 pt-8">
            @forelse($series as $single)
                @include('backend.series._card',[
                        'series'=> $single,
                        'route' => 'admin'
                        ])
            @empty
                <div class="dark:text-white text-2xl pt-10">
                    {{ __('homepage.series.no series found' )}}
                </div>
            @endforelse
        </div>

        <div class="w-full border-b-2 py-4 border-black dark:border-white">
            <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-end pb-2">
                <div class="text-2xl dark:text-white font-bold">{{ __('homepage.clips.Recently added!') }}</div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('frontend.clips.index') }}"
                       class="inline-flex justify-center items-center py-2 px-5 text-base font-medium text-center text-white
                              rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                        {{__('homepage.clips.more clips')}}
                        <svg class="w-3.5 h-3.5 ms-2 rtl:rotate-180" aria-hidden="true"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <ul class="flex-row">
            <div class="grid xl:grid-cols-4 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-4 pt-8">
                @forelse($clips as $clip)
                    <li class="my-2 w-full rounded-sm bg-white dark:bg-gray-900 p-4">
                        @include('backend.clips._card',[
                                'clip'=> $clip,
                                'route' => 'admin'
                                ])
                    </li>
                @empty
                    <div class="dark:text-white text-2xl">
                        {{ __('homepage.clips.no clips found' )}}
                    </div>
                @endforelse
            </div>
        </ul>
    </main>
@endsection
