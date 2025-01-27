@extends('layouts.frontend')

@section('content')
    <main class="container mx-auto mt-6 md:mt-12">
        @if($livestreams->count() == 0)
            <div class="dark:text-white text-2xl pt-10">
                {{ __('livestream.frontend.no active livestream found') }}
            </div>
        @else
            @php
                $publicLivestreams = $livestreams->filter(function($livestream){
                    return !is_null($livestream->clip_id);
                    });
                $hiddenLivestreams = $livestreams->filter(function($livestream){
                    return is_null($livestream->clip_id);
                });
            @endphp
            @if($publicLivestreams->count() > 0)
                <div class="flex items-center border-b-2 border-black dark:border-white pb-2">
                    <div class="grow">
                        <h2 class="text-2xl font-bold dark:text-white">
                            {{__('livestream.frontend.public livestreams available')}}
                        </h2>
                    </div>
                </div>
                <ul class="flex-row">
                    <div class="grid grid-cols-4 gap-4">
                        @foreach ($publicLivestreams as $livestream)
                            <li class="my-2 w-full rounded-sm bg-white dark:bg-gray-900 p-4">
                                @include('backend.clips._card',['clip'=> $livestream->clip])
                            </li>
                        @endforeach
                    </div>
                </ul>
            @endif
            @if($hiddenLivestreams->count() > 0)
                @can('administrate-assistant-pages')
                    <div class="flex items-center border-b-2 border-black dark:border-white pt-10 pb-2 ">
                        <div class="grow">
                            <h2 class="text-2xl font-bold dark:text-white">
                                {{ __('livestream.frontend.hidden livestreams') }}
                            </h2>
                        </div>
                    </div>
                    <ul class="flex-row">
                        <div class="grid grid-cols-2 gap-2 pt-6">
                            @foreach ($hiddenLivestreams as $livestream)
                                <li class="my-2 w-full rounded bg-white dark:bg-slate-800 p-4 flex justify-between
                                align-middle items-center"
                                >
                                    <div class="dark:text-white">
                                        {{ __('livestream.frontend.hidden livestream for room', [
                                                'roomName' => $livestream->name
                                         ]) }}
                                    </div>
                                    <div>
                                        <a href="{{route('livestreams.edit', $livestream)}}">
                                            <x-button type="button"
                                                      class="flex lg:basis-1/2 md:w-full content-center justify-between
                                              bg-blue-600 hover:bg-blue-700"
                                            >
                                                <div>
                                                    {{ __('livestream.frontend.visit livestream page') }}
                                                </div>
                                                <div>
                                                    <x-heroicon-o-arrow-right class="w-6" />
                                                </div>
                                            </x-button>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </div>
                    </ul>
                @endcan
            @endif
        @endif
    </main>
@endsection
