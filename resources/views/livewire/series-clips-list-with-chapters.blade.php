@use(App\Enums\Acl)
<div x-data="{ showCheckboxes: false, selectedClips: [], selected:{{ $defaultChapter }} } "
     x-init="$nextTick(() => { if (selected) { $refs['container' + selected].style.maxHeight = $refs['container' + selected].scrollHeight + 'px'; } })"
>
    <div class="mt-5 flex flex-col pb-4">
        <div class="flex space-x-2 items-center">
            <div class="dark:bg-gray-900">
                <label for="table-search" class="sr-only">Search</label>
                <div class="relative">
                    <input type="text"
                           wire:model.live="search"
                           id="table-search"
                           class="block pt-2 ps-10 text-md text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50
                   focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600
                   dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                           placeholder="{{ __('common.actions.search for episodes') }}">
                </div>
            </div>
            @if(str_contains(url()->current(), 'admin'))
                <div>
                    <x-button class="bg-green-500" @click="showCheckboxes = !showCheckboxes">
                        Toggle actions
                    </x-button>
                </div>
            @endif
        </div>
    </div>
    <ul class="shadow-box mb-4 flex flex-col w-full text-center text-lg dark:bg-gray-900 dark:text-white">
        @foreach($chapters as $chapter)
            <li class="relative flex flex-col sm:flex-row w-full rounded-lg pb-5">
                <div class="w-full">
                    <button type="button"
                            class="w-full px-4 sm:px-8 py-4 sm:py-6 text-left border-2 border-gray-600"
                            @click="selected !==  {{ $chapter->id }} ? selected = {{ $chapter->id }} : selected = null">
                        <div class="flex items-center justify-between">
                            <span>
                                {{$chapter->position}} -  {{$chapter->title}}
                            </span>
                            <x-heroicon-o-plus-circle class="h-6 w-6" />
                        </div>
                    </button>
                    <div class="relative max-h-0 overflow-hidden transition-all duration-700"
                         x-ref="container{{$chapter->id}}"
                         x-bind:style="selected == {{ $chapter->id }} ? 'max-height: ' + $refs['container' + {{ $chapter->id }}].scrollHeight + 'px' : ''">
                        <div class="p-4 sm:p-6">
                            <ul class="w-full pt-3">
                                @forelse($chapter->clips as $clip)
                                    @php
                                        $latestAsset = $clip->latestAsset();
                                    @endphp
                                    <li class="flex flex-col lg:flex-row content-center items-center rounded mb-4
                @if($clip->is_public) bg-gray-300 dark:bg-gray-700 @else bg-gray-500 dark:bg-blue-700 @endif
                p-2 text-center text-lg dark:text-white w-full">
                                        <!-- Episode Number -->
                                        <div class="flex">
                                            @if(str_contains(url()->current(), 'admin'))
                                                <div x-show="showCheckboxes">
                                                    <input type="checkbox" id="clip_{{ $clip->id }}"
                                                           name="clip_{{ $clip->id }}"
                                                           value="{{ $clip->id }}"
                                                           @change="event.target.checked ? selectedClips.push({{ $clip->id }}) : selectedClips = selectedClips.filter(id => id !== {{ $clip->id }})">
                                                </div>
                                            @endif
                                            <div class="w-full lg:w-4  md:mb-0 mx-8">
                                                {{ $clip->episode }}
                                            </div>
                                        </div>
                                        <div class="w-full lg:w-2/12 mb-2 md:mb-0">
                                            <div class="relative h-full w-full lg:w-48">
                                                <a href="@if(str_contains(url()->current(), 'admin')){{ route('clips.edit', $clip) }}@else{{ route('frontend.clips.show', $clip) }}@endif">
                                                    <img src="{{ fetchClipPoster($clip->latestAsset()?->player_preview) }}"
                                                         alt="preview image"
                                                         class="w-full"
                                                    >
                                                </a>
                                                <div class="absolute w-full py-2.5 bottom-0 inset-x-0 bg-blue-600 text-white
                                    text-md text-right pr-2 pb-2 leading-4">
                                                    {{ is_null($clip->latestAsset()) ? '00:00:00' : gmdate('H:i:s', $clip->latestAsset()->duration) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-full lg:w-3/12 mb-2 sm:mb-0 mx-2">{{ $clip->title }}</div>
                                        <div class="w-full lg:w-3/12 mb-2 sm:mb-0 mx-2">
                                            {{ $clip->presenters->pluck('full_name')->implode(', ') }}
                                        </div>
                                        <div class="w-full lg:w-1/12 mb-2 sm:mb-0">
                                            {{ $clip->recording_date->format('Y-m-d') }}
                                        </div>
                                        <div class="w-full lg:w-2/12 mb-2 sm:mb-0">{{ $clip->semester }}</div>
                                        <div class="w-full lg:w-2/12 flex justify-center items-center mb-2 sm:mb-0">
                                            <div class="pr-2">
                                                {{ ($clip->acls->isEmpty()) ? Acl::PUBLIC->lower() : $clip->acls->pluck('name')->implode(',') }}
                                            </div>
                                            @if($clip->acls->doesntContain(Acl::PUBLIC()) && $clip->acls->isNotEmpty())
                                                <div>
                                                    @can('watch-video', $clip)
                                                        <x-heroicon-o-lock-open class="h-4 w-4 text-green-500" />
                                                        <span class="sr-only">
                                    {{ __('common.unlocked') }} clip
                                </span>
                                                    @else
                                                        <x-heroicon-o-lock-closed class="h-4 w-4 text-red-700" />
                                                        <span class="sr-only">
                                    {{ __('common.locked') }} clip
                                </span>
                                                    @endcan
                                                </div>
                                            @endif
                                        </div>
                                        <div class="w-full lg:w-2/12 flex justify-center items-center mb-2 sm:mb-0">
                                            <div class="flex space-x-2">
                                                @foreach($clip->listAssetsByType() as $type)
                                                    @if($type == '1')
                                                        <x-iconoir-video-camera class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '2')
                                                        <x-iconoir-video-projector class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '3')
                                                        <x-iconoir-view-columns-2 class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '4')
                                                        <x-iconoir-music-double-note class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '5')
                                                        <x-iconoir-page class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '6')
                                                        <x-iconoir-closed-captions-tag class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @else
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="w-full lg:w-1/12 flex justify-center items-center mb-2 sm:mb-0">
                                            @if($dashboardAction && Request::segment(1) === 'admin')
                                                <div class="flex space-x-2">
                                                    <div>
                                                        <a href="{{ route('clips.edit', $clip) }}">
                                                            <x-button class="bg-blue-600 hover:bg-blue-700">
                                                                {{ __('common.actions.edit') }}
                                                            </x-button>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <x-modals.delete :route="route('clips.destroy', $clip)">
                                                            <x-slot:title>
                                                                {{__('clip.backend.delete.modal title',['clip_title'=>$clip->title])}}
                                                            </x-slot:title>
                                                            <x-slot:body>
                                                                {{__('clip.backend.delete.modal body')}}
                                                            </x-slot:body>
                                                        </x-modals.delete>
                                                    </div>
                                                </div>

                                            @else
                                                <form method="GET" action="{{ route('frontend.clips.show', $clip) }}">
                                                    <button type="submit"
                                                            class="focus:outline-none text-white text-sm py-1.5 px-5 rounded-md
                                            bg-blue-700 dark:bg-white hover:bg-blue-500 dark:hover:bg-gray-600
                                            hover:shadow-lg"
                                                    >
                                                        <x-heroicon-o-play class="h-6 w-6 dark:text-gray-900" />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                    </li>

                                @empty
                                    <div class="grid place-items-center w-full">
                                        <div class="mb-4 w-full rounded bg-gray-200 dark:bg-slate-800 p-5 text-center text-sm sm:text-2xl dark:text-white">
                                            {!!  __('series.backend.Series chapter has no clips', ['chapterTitle' =>  $chapter->title]) !!}
                                        </div>
                                    </div>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
        @php

                @endphp
        @if($clipsWithoutChapter->isNotEmpty())
            <li class="relative flex flex-col sm:flex-row w-full rounded-lg pb-5">
                <div class="w-full">
                    <button type="button"
                            class="w-full px-4 sm:px-8 py-4 sm:py-6 text-left border-2 border-gray-600"
                            @click="selected !== 1 ? selected = 1 : selected = null">
                        <div class="flex items-center justify-between">
                            <span>
                              {{ __('series.common.clips without chapter(s)') }}
                            </span>
                            <x-heroicon-o-plus-circle class="h-6 w-6" />
                        </div>
                    </button>
                    <div class="relative max-h-0 overflow-hidden transition-all duration-700"
                         x-ref="container1"
                         x-bind:style="selected == 1 ? 'max-height: ' + $refs.container1.scrollHeight + 'px' : ''">
                        <div class="p-4 sm:p-6">
                            <ul class="w-full pt-3">
                                @forelse($clipsWithoutChapter as $key=>$clip)
                                    <li class="flex flex-col lg:flex-row content-center items-center rounded mb-4
                @if($clip->is_public) bg-gray-300 dark:bg-gray-700 @else bg-gray-500 dark:bg-blue-700 @endif
                p-2 text-center text-lg dark:text-white w-full">
                                        <!-- Episode Number -->
                                        <div class="flex">
                                            @if(str_contains(url()->current(), 'admin'))
                                                <div x-show="showCheckboxes">
                                                    <input type="checkbox" id="clip_{{ $clip->id }}"
                                                           name="clip_{{ $clip->id }}"
                                                           value="{{ $clip->id }}"
                                                           @change="event.target.checked ? selectedClips.push({{ $clip->id }}) : selectedClips = selectedClips.filter(id => id !== {{ $clip->id }})">
                                                </div>
                                            @endif
                                            <div class="w-full lg:w-4  md:mb-0 mx-8">
                                                {{ $clip->episode }}
                                            </div>
                                        </div>
                                        <div class="w-full lg:w-2/12 mb-2 md:mb-0">
                                            <div class="relative h-full w-full lg:w-48">
                                                <a href="@if(str_contains(url()->current(), 'admin')){{ route('clips.edit', $clip) }}@else{{ route('frontend.clips.show', $clip) }}@endif">
                                                    <img src="{{ fetchClipPoster($clip->latestAsset()?->player_preview) }}"
                                                         alt="preview image"
                                                         class="w-full"
                                                    >
                                                </a>
                                                <div class="absolute w-full py-2.5 bottom-0 inset-x-0 bg-blue-600 text-white
                                    text-md text-right pr-2 pb-2 leading-4">
                                                    {{ is_null($clip->latestAsset()) ? '00:00:00' : gmdate('H:i:s', $clip->latestAsset()->duration) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-full lg:w-3/12 mb-2 sm:mb-0 mx-2">{{ $clip->title }}</div>
                                        <div class="w-full lg:w-3/12 mb-2 sm:mb-0 mx-2">
                                            {{ $clip->presenters->pluck('full_name')->implode(', ') }}
                                        </div>
                                        <div class="w-full lg:w-1/12 mb-2 sm:mb-0">
                                            {{ $clip->recording_date->format('Y-m-d') }}
                                        </div>
                                        <div class="w-full lg:w-2/12 mb-2 sm:mb-0">{{ $clip->semester }}</div>
                                        <div class="w-full lg:w-2/12 flex justify-center items-center mb-2 sm:mb-0">
                                            <div class="pr-2">
                                                {{ ($clip->acls->isEmpty()) ? Acl::PUBLIC->lower() : $clip->acls->pluck('name')->implode(',') }}
                                            </div>
                                            @if($clip->acls->doesntContain(Acl::PUBLIC()) && $clip->acls->isNotEmpty())
                                                <div>
                                                    @can('watch-video', $clip)
                                                        <x-heroicon-o-lock-open class="h-4 w-4 text-green-500" />
                                                        <span class="sr-only">
                                    {{ __('common.unlocked') }} clip
                                </span>
                                                    @else
                                                        <x-heroicon-o-lock-closed class="h-4 w-4 text-red-700" />
                                                        <span class="sr-only">
                                    {{ __('common.locked') }} clip
                                </span>
                                                    @endcan
                                                </div>
                                            @endif
                                        </div>
                                        <div class="w-full lg:w-2/12 flex justify-center items-center mb-2 sm:mb-0">
                                            <div class="flex space-x-2">
                                                @foreach($clip->listAssetsByType() as $type)
                                                    @if($type == '1')
                                                        <x-iconoir-video-camera class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '2')
                                                        <x-iconoir-video-projector class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '3')
                                                        <x-iconoir-view-columns-2 class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '4')
                                                        <x-iconoir-music-double-note class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '5')
                                                        <x-iconoir-page class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @elseif($type == '6')
                                                        <x-iconoir-closed-captions-tag class="w-5 sm:w-6 h-5 sm:h-6" />
                                                    @else
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="w-full lg:w-1/12 flex justify-center items-center mb-2 sm:mb-0">
                                            @if($dashboardAction && Request::segment(1) === 'admin')
                                                <div class="flex space-x-2">
                                                    <div>
                                                        <a href="{{ route('clips.edit', $clip) }}">
                                                            <x-button class="bg-blue-600 hover:bg-blue-700">
                                                                {{ __('common.actions.edit') }}
                                                            </x-button>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <x-modals.delete :route="route('clips.destroy', $clip)">
                                                            <x-slot:title>
                                                                {{__('clip.backend.delete.modal title',['clip_title'=>$clip->title])}}
                                                            </x-slot:title>
                                                            <x-slot:body>
                                                                {{__('clip.backend.delete.modal body')}}
                                                            </x-slot:body>
                                                        </x-modals.delete>
                                                    </div>
                                                </div>

                                            @else
                                                <form method="GET" action="{{ route('frontend.clips.show', $clip) }}">
                                                    <button type="submit"
                                                            class="focus:outline-none text-white text-sm py-1.5 px-5 rounded-md
                                            bg-blue-700 dark:bg-white hover:bg-blue-500 dark:hover:bg-gray-600
                                            hover:shadow-lg"
                                                    >
                                                        <x-heroicon-o-play class="h-6 w-6 dark:text-gray-900" />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                    </li>
                                @empty
                                    <div class="grid place-items-center w-full">
                                        <div class="mb-4 w-full rounded bg-gray-200 dark:bg-slate-800 p-5 text-center text-sm sm:text-2xl dark:text-white">
                                            {{ __('series.common.no clips') }}
                                        </div>
                                    </div>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
        @endif
    </ul>
    <div x-show="showCheckboxes">
        <div class="pt-10">
            <form method="POST" action="{{ route('series.clips.batch.delete.multiple.clips', $series) }}">
                @csrf
                <!-- Hidden input to store selected clip IDs -->
                <input type="hidden" name="clip_ids" :value="JSON.stringify(selectedClips)">
                <div>
                    <x-button class="bg-red-500"
                              x-bind:disabled="selectedClips.length === 0"
                              @click="alert('Are you sure you want to delete ' + selectedClips.length + ' clips?')">
                        Delete <span x-text="selectedClips.length" class="px-2"></span> clips
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>