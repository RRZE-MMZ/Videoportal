@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
    >
        Edit Collection
    </div>

    <div class="flex px-2 py-2">
        <form action="{{ route('collections.update',$collection) }}"
              method="POST"
              class="w-4/5">
            @csrf
            @method('PATCH')
            <div class="flex flex-col gap-6">

                <x-form.input field-name="position"
                              input-type="number"
                              :value="$collection->position"
                              label="Position"
                              :full-col="false"
                              :required="false"
                />

                <x-form.input field-name="title"
                              input-type="text"
                              :value="$collection->title"
                              label="Title"
                              :full-col="true"
                              :required="true"
                />

                <x-form.textarea field-name="description"
                                 :value="$collection->description"
                                 label="Description"
                />

                <x-form.toggle-button :value="$collection->is_public"
                                      label="Public available"
                                      field-name="is_public"
                />
                <div class="col-span-7 w-4/5 space-x-4">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        Update collection
                    </x-button>
                    <x-back-button :url="route('collections.index')" class="bg-green-600 hover:bg-green-700">
                        Back to collections index
                    </x-back-button>
                </div>
            </div>
        </form>
    </div>
    <div class="py-10 mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
    >
        Collection options
    </div>
    <div class="pt-10 mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
    >
        Toggle clips
    </div>
    <div class="flex pt-6">
        <form action="{{route('collections.toggleClips',$collection)}}"
              method="POST"
              class="w-4/5">
            @csrf
            <div class="flex flex-col gap-6">
                <x-form.select2-multiple field-name="ids"
                                         label="Clips"
                                         select-class="select2-tides-clips"
                                         :model="null"
                                         :items="[]"
                                         :full-col="true"
                                         :required="true"
                />
                <x-form.button :link="$link=false"
                               type="submit"
                               text="Toggle selected clips"
                />
            </div>
        </form>
    </div>
    <div class="pt-10                     mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
    >
        Collection Clips
    </div>
    <div class="mt-5 flex flex-col">
        <ul class="w-full pt-3">
            @forelse($collection->clips as $clip)
                <li class="flex flex-col lg:flex-row content-center items-center rounded mb-4
                @if($clip->is_public) bg-gray-300 dark:bg-gray-700 @else bg-gray-500 dark:bg-blue-700 @endif
                p-2 text-center text-lg dark:text-white w-full">
                    <div class="w-full lg:w-2/12 mb-2 sm:mb-0">
                        {{ $clip->episode }}
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
                        <div class="flex space-x-2">
                            <div>
                                <a href="{{ route('clips.edit', $clip) }}">
                                    <x-button class="bg-blue-600 hover:bg-blue-700">
                                        {{ __('common.actions.edit') }}
                                    </x-button>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <div class="grid place-items-center">
                    <div class="mb-4 w-full rounded-sm bg-gray-200 dark:bg-slate-800 p-5 text-center text-2xl dark:text-white">
                        {{ __('series.common.no clips') }}
                    </div>
                </div>
            @endforelse
            <div class="pt-10 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <x-button class="bg-blue-600 hover:bg-blue-700">
                    {{ __('series.backend.actions.reorder series clips') }}
                </x-button>
                <x-back-button :url="route('collections.edit', $collection)"
                               class="bg-green-600 hover:bg-green-700">
                    {{ __('common.forms.go back') }}
                </x-back-button>
            </div>
        </ul>
    </div>
@endsection
