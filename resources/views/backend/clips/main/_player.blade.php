<div class="flex flex-row ">
    <div class="w-2/3 pb-6 items-center align-middle mx-auto">
        <div>
            <x-player :clip="$clip" :wowzaStatus="$wowzaStatus"
                      :default-video-url="$defaultVideoUrl" />
        </div>
    </div>
    <div class="w-1/3">
        <div class="mt-5 flex" x-data="{ selected: 0 }"
             x-init="selected === 0 ? $refs.container0.style.maxHeight = $refs.container0.scrollHeight + 'px' : ''">
            <ul class="shadow-box mb-4 flex flex-col w-full text-center text-lg dark:bg-gray-900 dark:text-white px-4">

                <li class="relative flex w-full rounded-lg pb-5">
                    <div class="w-full">
                        <button type="button" class="w-full px-8 py-6 text-left  border-2 border-gray-600"
                                @click="selected !==  0  ? selected = 0 : selected = null">
                            <div class="flex items-center justify-between">
                                <span>
                                  {{ __('player.backend.quick links') }}
                                </span>
                                <x-heroicon-o-plus-circle class="h-6 w-6" />
                            </div>
                        </button>
                        <div class="relative max-h-0 overflow-hidden transition-all duration-700"
                             x-ref="container0"
                             x-bind:style="selected == 0 ? 'max-height: ' + $refs.container0.scrollHeight + 'px' : ''">
                            <div class="p-6 w-full">
                                <div class="flex flex-col space-y-4 dark:text-white w-full ">
                                    <a href="{{ route('frontend.clips.show', $clip) }}" class=" items-center px-4">
                                        <x-button type="button"
                                                  class="bg-green-600 hover:bg-green-700 w-full"
                                        >
                                            <x-heroicon-o-arrow-right-circle class="w-6 h-6" />
                                            <div class="pl-2">
                                                {{ __('clip.backend.actions.go to clip public page') }}
                                            </div>
                                        </x-button>
                                    </a>
                                    <a href="{{ route('statistics.clip', $clip) }}" class=" items-center px-4">
                                        <x-button type="button"
                                                  class="bg-green-600 hover:bg-green-700 w-full"
                                        >
                                            <x-heroicon-o-arrow-right-circle class="w-6 h-6" />
                                            <div class="pl-2">
                                                {{ __('clip.backend.actions.got to clip statistics page') }}
                                            </div>
                                        </x-button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @if($clip->is_livestream)
                    <li class="relative flex w-full rounded-lg pb-5">
                        <div class="w-full">
                            <button type="button" class="w-full px-8 py-6 text-left  border-2 border-gray-600"
                                    @click="selected !==  1  ? selected = 1 : selected = null">
                                <div class="flex items-center justify-between">
                                <span>
                                  Livestream Info
                                </span>
                                    <x-heroicon-o-plus-circle class="h-6 w-6" />
                                </div>
                            </button>
                            <div class="relative max-h-0 overflow-hidden transition-all duration-700"
                                 x-ref="container1"
                                 x-bind:style="selected == 1 ? 'max-height: ' + $refs.container1.scrollHeight + 'px' : ''">
                                <div class="p-6 w-full">
                                    @if($clip->livestream)
                                        <div class="flex flex-col space-y-4 dark:text-white w-full ">
                                            <form
                                                    action="{{route('livestreams.cancelReservation', $clip->livestream->id)}}"
                                                    method="POST">
                                                @csrf
                                                <div class="mx-4 ">
                                                    <div class="border-b mb-4">Active livestream room
                                                        : {{ $clip->livestream->name }}
                                                        <span class="italic text-sm"
                                                        >
                                                                        since {{ $clip->livestream->time_availability_start }}
                                                                    </span>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col dark:text-white w-full ">
                                                    <div class="items-center px-4">
                                                        <x-button type="submit"
                                                                  class="bg-blue-600 hover:bg-blue-700 w-full">
                                                            Stop reservation
                                                        </x-button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @else
                                        @can('administrate-assistant-pages')
                                            <div class="flex flex-col space-y-4 dark:text-white w-full ">
                                                <form action="{{ route('livestreams.makeReservation') }}"
                                                      method="POST"
                                                >
                                                    @csrf
                                                    <div class="mx-4 items-center ">
                                                        <div class="flex flex-col border-b">
                                                            <div class="text-lg font-bold">
                                                                Livestream room reservation options *
                                                            </div>
                                                            <span class="py-1 italic text-sm">
                                             *If a room is already reserved is not going to be listed
                                        </span>
                                                        </div>
                                                        <div>

                                                        </div>
                                                        <input id="clipID"
                                                               type="text"
                                                               name="clipID"
                                                               value="{{ old('clipID',$clip->id) }}"
                                                               class="@error('clipID') is-invalid @enderror hidden">
                                                        @error('clipID')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                        <x-form.select2-single field-name="livestreamID"
                                                                               label="Room name"
                                                                               select-class="select2-tides"
                                                                               model="livestream"
                                                                               columns="4"
                                                                               column-start="2"
                                                                               column-end="4"
                                                                               :selectedItem="old('livestreamID')"
                                                        />
                                                    </div>

                                                    <div class="flex flex-col dark:text-white w-full ">
                                                        <div class="items-center px-4">
                                                            <x-button type="submit"
                                                                      class="bg-blue-600 hover:bg-blue-700 w-full"
                                                            >
                                                                <x-heroicon-o-arrow-right-circle class="w-6 h-6" />
                                                                <div class="pl-2">
                                                                    Livestream reservieren
                                                                </div>
                                                            </x-button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @else
                    <li class="relative flex w-full rounded-lg pb-5">
                        <div class="w-full">
                            <button type="button" class="w-full px-8 py-6 text-left  border-2 border-gray-600 py-4"
                                    @click="selected !==  1  ? selected = 1 : selected = null">
                                <div class="flex items-center justify-between">
                                <span>
                                    {{ __('player.backend.preview image') }}
                                </span>
                                    <x-heroicon-o-plus-circle class="h-6 w-6" />
                                </div>
                            </button>
                            <div class="relative max-h-0 overflow-hidden transition-all duration-700"
                                 x-ref="container1"
                                 x-bind:style="selected == 1 ? 'max-height: ' + $refs.container1.scrollHeight+100 + 'px' : ''">
                                <div class="p-6 w-full">
                                    <div class="flex flex-row w-full">
                                        <div class="flex flex-row w-1/3">
                                            <div class="w-48 h-48 overflow-hidden">
                                                <img src="{{ fetchClipPoster($clip->latestAsset()->player_preview)  }}"
                                                     alt="poster image" class="object-contain w-full h-full">
                                            </div>
                                        </div>
                                        <div class="flex flex-col w-2/3">
                                            <div>
                                                <form action="{{ route('clips.generatePreviewImageFromFrame', $clip) }}"
                                                      method="POST"
                                                      class="space-y-4 flex flex-col space-x-2">
                                                    @csrf
                                                    <input id="currentTime" type="text" name="recentFrame"
                                                           class="dark:text-black h-200">
                                                    <x-button type="submit" class="py-2 bg-blue-600 hover:bg-blue-700">
                                                        {{ __('clip.backend.actions.generate preview from frame') }}
                                                    </x-button>
                                                </form>
                                            </div>
                                            <div class="flex flex-nowrap items-center justify-center space-x-2">
                                                <span class="h-px w-20 bg-gray-300"></span>
                                                <span class="dark:text-white uppercase">
                                                    {{ __('common.or') }}
                                                </span>
                                                <span class="h-px w-20 bg-gray-300"></span>
                                            </div>
                                            <div>
                                                <form action="{{ route('clips.generatePreviewImageFromUser', $clip) }}"
                                                      method="POST"
                                                      class="px-2">
                                                    @csrf
                                                    <input type="file" name="image" class="filepond"
                                                           data-max-file-size="10MB">
                                                    @error('image')
                                                    <div class="col-start-2 col-end-6">
                                                        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
                                                    </div>
                                                    @enderror
                                                    <x-button class="bg-blue-600 hover:bg-blue-700 ">
                                                        <div class="flex">
                                                            <x-heroicon-o-arrow-up-circle
                                                                    class="h-6 w-6"></x-heroicon-o-arrow-up-circle>
                                                            <span class="pl-4">
                                                                {{ __('clip.backend.actions.upload an image') }}
                                                            </span>
                                                        </div>
                                                    </x-button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="relative flex w-full rounded-lg pb-5">
                        <div class="w-full">
                            <button type="button" class="w-full px-8 py-6 text-left  border-2 border-gray-600 py-4"
                                    @click="selected !==  2  ? selected = 2 : selected = null">
                                <div class="flex items-center justify-between">
                                <span>
                                    {{ __('player.backend.load different video assets') }}
                                </span>
                                    <x-heroicon-o-plus-circle class="h-6 w-6" />
                                </div>
                            </button>
                            <div class="relative max-h-0 overflow-hidden transition-all duration-700"
                                 x-ref="container2"
                                 x-bind:style="selected == 2 ? 'max-height: ' + $refs.container2.scrollHeight + 'px' : ''">
                                <div class="p-6 w-full">
                                    @foreach($alternativeVideoUrls as $type=> $url)
                                        <div class="mx-4 w-full px-4 pb-2">
                                            @if($type === 'composite')
                                                <a href="{{$url}}"
                                                   class="video-link w-full flex px-4 py-2 bg-blue-800 border border-transparent rounded-md
                    font-semibold text-xs text-white uppercase tracking-widest
                    hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900
                    focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 items-center"
                                                   title="composite video stream">
                                                    <x-heroicon-o-view-columns class="w-6 h-6 fill-white" />
                                                    <div class="pl-2">
                                                        {{ __('player.common.sbs') }}
                                                    </div>
                                                </a>
                                            @endif
                                            @if($type === 'presenter')
                                                <a href="{{$url}}"
                                                   class="video-link flex px-4 py-2 bg-blue-800 border border-transparent rounded-md
                    font-semibold text-xs text-white uppercase tracking-widest
                    hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900
                    focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 items-center"
                                                   title="presenter video stream place-items-center">
                                                    <x-heroicon-o-user class="w-6 h-6 fill-white" />
                                                    <div class="pl-2">
                                                        {{ __('player.common.camera') }}
                                                    </div>
                                                </a>
                                            @endif
                                            @if($type === 'presentation')
                                                <a href="{{$url}}"
                                                   class="video-link flex px-4 py-2 bg-blue-800 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                           hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900
                                           focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out
                                           duration-150 items-center"
                                                   title="presentation video stream">
                                                    <x-heroicon-o-computer-desktop class="w-6 h-6 fill-white" />
                                                    <div class="pl-2">
                                                        {{ __('player.common.slides') }}
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
