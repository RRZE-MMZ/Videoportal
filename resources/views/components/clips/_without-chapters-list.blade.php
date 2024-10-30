@use(App\Enums\Acl)
<div class="mt-5 flex flex-col">
    @if($reorder)
        <form class="w-full" action="{{ route('series.clips.reorder', $series) }}" method="POST">
            @csrf
            @endif
            <ul class="w-full pt-3">
                @forelse($clips as $clip)
                    <li class="flex flex-col lg:flex-row content-center items-center rounded mb-4
                @if($clip->is_public) bg-gray-300 dark:bg-gray-700 @else bg-gray-500 dark:bg-blue-700 @endif
                p-2 text-center text-lg dark:text-white w-full">
                        <div class="w-full lg:w-4  md:mb-0 mx-8">
                            @if ($reorder)
                                <label class="flex flex-col sm:flex-row items-center">
                                    <input class="w-full sm:w-1/2 dark:text-black" type="number"
                                           name="episodes[{{ $clip->id }}]"
                                           value="{{ $loop->index + 1 }}"
                                    >
                                    <div class="mt-2 sm:mt-0 sm:ml-2">
                                        <p class="text-sm text-green-500 dark:text-green-200">
                                            {{ __('series.backend.actual episode') }} {{ $clip->episode }}
                                        </p>
                                    </div>
                                </label>
                                @error('episodes')
                                <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            @else
                                {{ $clip->episode }}
                            @endif
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
                        <div class="w-full lg:w-1/12">
                            @if($dashboardAction && Request::segment(1) === 'admin')
                                <a href="{{ route('clips.edit', $clip) }}">
                                    <x-button class="bg-blue-600 hover:bg-blue-700">
                                        {{ __('common.actions.edit') }}
                                    </x-button>
                                </a>
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
                    <div class="grid place-items-center">
                        <div class="mb-4 w-full rounded bg-gray-200 dark:bg-slate-800 p-5 text-center text-2xl dark:text-white">
                            {{ __('series.common.no clips') }}
                        </div>
                    </div>
                @endforelse
                @if($reorder)
                    <div class="pt-10 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('series.backend.actions.reorder series clips') }}
                        </x-button>
                        <x-back-button :url="route('series.edit', $series)"
                                       class="bg-green-600 hover:bg-green-700">
                            {{ __('common.forms.go back') }}
                        </x-back-button>
                    </div>
                @endif
            </ul>
            @if($reorder)
        </form>
    @endif
</div>
