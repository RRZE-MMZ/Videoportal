@extends('layouts.backend')

@section('content')
    <div>
        <div class=" pt-10 mb-5 flex items-center border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
        >
            Livestream information
        </div>
        @if($livestream->active)
            <div class="flex flex-row pt-6 space-x-2">
                <div class="w-2/3 pb-6 items-center align-middle mx-auto">
                    <div>
                        <div id="player" data-plyr-provider="html5" data-plyr-embed-id="video">
                            <video id="video" class="player-container" playsinline controls
                                   data-poster="/images/generic_clip_poster_image.png">
                                <source
                                    src="{{ $livestreamURL }}"
                                    type="video/mp4"
                                />
                            </video>
                        </div>
                    </div>
                </div>
                <div class="w-1/3 pl-4">
                    <div class="flex flex-col space-y-4 dark:text-white w-full justify-items-center">
                        <div>
                            <h4>
                                Livestream info
                            </h4>
                            <p>
                            @if($livestream->clip_id)
                                <div class="italic dark:text-white ">
                                    Livestream is reserved to this Clip -> <a href=""> <a
                                            href="{{ route('clips.edit', $livestream->clip) }}">
                                            {{ $livestream->clip->title }}
                                        </a></a>
                                </div>

                            @else
                                <div class="italic dark:text-white">
                                    Livestream is active but with not bind with a clip
                                </div>
                                @endif
                                </p>

                                <ul class="space-y-4 text-left pt-10">
                                    <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                        <svg class="shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400"
                                             aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 16 12">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5" />
                                        </svg>
                                        <span>      SBS Signal Status : Incoming Signal aus Hörsaal </span>
                                    </li>
                                    <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                        <svg class="shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400"
                                             aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 16 12">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5" />
                                        </svg>
                                        <span>Kamera Signal Status : Incoming Signal aus Hörsaal</span>
                                    </li>
                                    <li class="flex items-center space-x-3 rtl:space-x-reverse">
                                        <svg class="shrink-0 w-3.5 h-3.5 text-gray-500 dark:text-gray-400"
                                             aria-hidden="true"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                             viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                                        </svg>


                                        <span>  Folien Signal Status : Incoming Signal aus Hörsaal</span></span>
                                    </li>
                                </ul>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-4 dark:text-white w-full justify-items-center items-center pt-10">
                        <div class="flex flex-col space-y-4 dark:text-white w-full ">
                            <form action="{{route('livestreams.cancelReservation', $livestream)}}" method="POST">
                                @csrf
                                <x-button type="submit"
                                          class="bg-green-600 hover:bg-green-700 "
                                >
                                    <x-heroicon-c-x-mark class="w-6 h-6" />
                                    <div class="pl-2">
                                        Stop reservation
                                    </div>
                                </x-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    @else
        <div class="italic dark:text-white ">
            Livestream room is not activated
        </div>
    @endif
    @can('administrate-admin-portal-pages')
        <div class="flex items-center border-b border-black pt-10 font-semibold font-2xl
                dark:text-white dark:border-white"
        >
            Edit Livestream: {{ $livestream->name }}
            <span
                class=" pl-2 text-sm text-yellow-500">
            active till {{ $livestream->time_availability_end }}
        </span>
        </div>
        <div class="flex px-2 py-2">
            <form action="{{ route('livestreams.update',$livestream) }}"
                  method="POST" class="w-4/5">
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-3">
                    <x-form.input field-name="name"
                                  input-type="text"
                                  :value="old('name', $livestream->name)"
                                  label="Livestream name"
                                  :full-col="true"
                                  :required="true"
                    />

                    <x-form.input field-name="opencast_location_name"
                                  input-type="text"
                                  :value="old('name', $livestream->opencast_location_name)"
                                  label="Opencast location name"
                                  :full-col="true"
                                  :required="true"
                    />

                    <x-form.input field-name="app_name"
                                  input-type="text"
                                  :value="old('app_name', $livestream->app_name)"
                                  label="Wowza App name"
                                  :full-col="true"
                                  :required="true"
                    />

                    <x-form.input field-name="content_path"
                                  input-type="text"
                                  :value="old('content_path', $livestream->content_path)"
                                  label="Wowza content path"
                                  :full-col="true"
                                  :required="true"
                    />

                    <x-form.toggle-button :value="$livestream->has_transcoder"
                                          label="Transcoder"
                                          field-name="has_transcoder"
                    />

                    <div class="col-span-7 mt-10 w-4/5 space-x-4">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            Update livestream infos
                        </x-button>
                        <a href="{{route('livestreams.index')}}">
                            <x-button type="button" class="bg-gray-600 hover:bg-gray:700">
                                Back to livestreams list
                            </x-button>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        @endcan
        </div>
        @endsection
