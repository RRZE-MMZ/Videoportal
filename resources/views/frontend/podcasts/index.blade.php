@php use Carbon\Carbon;use Illuminate\Support\Facades\URL; @endphp
@extends('layouts.frontend')

@section('content')
    <main class="container mx-auto mt-6 sm:mt-8 md:mt-12 h-auto px-4 sm:px-6">
        <div class="pb-2">
            @include('frontend.search._searchbar')
        </div>
        <section class="dark:bg-gray-900">
            <div class="py-4 sm:py-6 px-4 sm:px-6 mx-auto max-w-(--breakpoint-xl) lg:py-16 lg:px-8">
                <div class="mx-auto max-w-(--breakpoint-lg) text-center mb-6 sm:mb-8 lg:mb-16">
                    <h2 class="mb-3 text-2xl sm:text-3xl tracking-tight font-extrabold text-gray-900 dark:text-white">
                        {{ __('podcast.frontend.jumbotron heading') }}
                    </h2>
                    <p class="font-light text-gray-700 text-base sm:text-lg lg:mb-16 dark:text-gray-400">
                        {{ __('podcast.frontend.jumbotron body') }}
                    </p>
                </div>
                <div class="flex w-full">
                    @include('layouts.breadcrumbs')
                </div>

                <div class="grid gap-6 sm:gap-8 mb-6 lg:mb-16 grid-cols-1 sm:grid-cols-2 md:grid-cols-2">
                    @forelse($podcasts as $podcast)
                        @include('partials.podcasts._card', $podcast)
                    @empty
                        <div class="dark:text-white font-bold text-xl sm:text-2xl text-center">
                            {{ __('podcast.frontend.no podcasts available or published') }}
                        </div>
                    @endforelse
                </div>
                <div>
                    <div class="py-8 sm:py-10">
                        {{ $podcasts->links() }}
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
