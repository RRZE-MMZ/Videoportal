@use(App\Enums\Acl)
@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal pb-2">
        <div class="font-semibold ">
            {!! __('series.backend.reorder clips for series', ['series_title' => $series->title]) !!}
        </div>
    </div>
    @if ($series->chapters()->count() > 0)
        <div class="flex pt-10 flex-col ">
            <div class="flex h4 font-extrabold italic bg-yellow-200 rounded-2xl m-4 p-4 items-center">
                <div>
                    <x-iconoir-info-circle class="w-6 h-6" />
                </div>
                <div class="pl-4">
                    {{ __('series.backend.reorder series clips with chapters info') }}
                </div>
            </div>
            <div class="pt-10">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('series.chapters.index', $series) }}">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('series.backend.actions.manage chapters') }}
                        </x-button>
                    </a>
                    <a href="{{ route('series.edit', $series) }}">
                        <x-button class="bg-green-600 hover:bg-green-700">
                            {{ __('series.backend.actions.back to edit series') }}
                        </x-button>
                    </a>
                </div>

            </div>
        </div>
    @else
        <div class="mt-5 flex flex-col">
            <form class="w-full" action="{{ route('series.clips.reorder', $series) }}" method="POST">
                @csrf
                <ul class="w-full pt-3">
                    @forelse($clips as $clip)
                        <li class="flex flex-col lg:flex-row content-center items-center rounded mb-4
                @if($clip->is_public) bg-gray-300 dark:bg-gray-700 @else bg-gray-500 dark:bg-blue-700 @endif
                p-2 text-center text-lg dark:text-white w-full">
                            <div class="flex flex-col">
                                <input class="w-full sm:w-1/2 dark:text-black" type="number"
                                       name="episodes[{{ $clip->id }}]"
                                       value="{{ $loop->index + 1 }}"
                                >
                                <div class="pt-4 text-sm place-content-center items-center text-left">
                                    Current episode: {{ $clip->episode }}
                                </div>
                                @error('episodes')
                                <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
                                @enderror
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
                            <div class="mb-4 w-full rounded bg-gray-200 dark:bg-slate-800 p-5 text-center text-2xl dark:text-white">
                                {{ __('series.common.no clips') }}
                            </div>
                        </div>
                    @endforelse
                    <div class="pt-10 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('series.backend.actions.reorder series clips') }}
                        </x-button>
                        <x-back-button :url="route('series.edit', $series)"
                                       class="bg-green-600 hover:bg-green-700">
                            {{ __('common.forms.go back') }}
                        </x-back-button>
                    </div>
                </ul>
            </form>
        </div>
    @endif

@endsection
