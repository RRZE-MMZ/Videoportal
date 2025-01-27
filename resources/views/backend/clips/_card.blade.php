@use(App\Enums\Acl)
@php
    $latestAsset = $clip->latestAsset();
@endphp
<div class="relative my-2 bg-gray-50 dark:bg-slate-800 rounded-md dark:border-white font-normal">
    <div class="relative overflow-hidden">
        <a href="@if (str_contains(url()->current(), 'admin')) {{route('clips.edit', $clip)}}
                @else {{route('frontend.clips.show', $clip) }} @endif">
            <img
                    src="{{ ($latestAsset)
                    ? fetchClipPoster($latestAsset?->player_preview)
                    : "/images/generic_clip_poster_image.png" }}"
                    alt="preview image"
                    class="object-cover w-full h-full" />
        </a>
        <div
                class="absolute w-full py-2.5 bottom-0 inset-x-0 bg-blue-600  text-white
                    text-xs text-right pr-2 pb-2 leading-4 ">
            {{ $latestAsset?->durationToHours() }}
        </div>
    </div>

    <div class="flex-row justify-between p-2 mb-6 w-full bg-gray-50 dark:bg-slate-800        pb-7">
        <div class="mb-1">
            <div class="text-md font-bold text-gray-900 dark:text-white">
                <a
                        href="@if (str_contains(url()->current(), 'admin')) {{route('clips.edit', $clip)}}
                @else {{route('frontend.clips.show', $clip) }} @endif"
                        class="text-md"
                >
                    {{ (request()->routeIs('clips.index') ||
                      request()->routeIs('frontend.clips.index') ||
                      request()->routeIs('search'))
                     ?$clip->title
                     :Str::limit($clip->title, 100, preserveWords:true)}}
                </a>
            </div>
            <div>
                @if ($clip->owner)
                    <span class="text-sm italic dark:text-white">von {{ $clip->owner?->getFullNameAttribute() }}</span>
                @endif
            </div>
            <p class="text-base text-gray-700 dark:text-white">
                {{ strip_tags(str_contains(url()->current(),'search')
                    ?$clip->description
                    :Str::limit($clip->description, 30)) }}
            </p>
        </div>

        @if($clip->presenters->isNotEmpty())
            <div class="flex items-center pt-2 justify-content-between ">
                <div class="pr-2 dark:text-white">
                    <x-heroicon-o-user class="h-4 w-4" />
                </div>
                <div class="text-sm">
                    <p class="italic text-gray-900 dark:text-white ">
                        {{ $clip->presenters
                            ->map(function($presenter){
                                return $presenter->getFullNameAttribute();
                            })->implode(',') }}
                    </p>
                </div>
            </div>
        @endif
        <div class="flex items-center justify-content-between">

            <div class="pr-2">
                <x-heroicon-o-clock class="w-4 h-4 dark:text-white" />
            </div>
            <div class="text-sm">
                <p class="italic text-gray-900 dark:text-white">
                    {{ $clip->recording_date->diffForHumans()  }}
                </p>
            </div>
        </div>

        @if($clip->acls->isNotEmpty() )
            <div class="flex items-center pt-2 justify-content-between">
                <div class="pr-2">
                    @if(!$clip->acls->contains(Acl::PUBLIC))
                        @can('watch-video', $clip)
                            <x-heroicon-o-lock-open class="h-4 w-4 text-green-500 dark:text-white" />
                            <span class="sr-only">{{ __('common.unlocked') }}</span>
                        @else
                            <x-heroicon-o-lock-closed class="h-4 w-4 text-red-700 dark:text-white dark:bg-gray-50" />
                            <span class="sr-only">{{ __('common.locked') }}</span>
                        @endcan
                    @endif
                </div>
                <div class="text-sm">
                    <p class="italic text-gray-900 dark:text-white">
                        {{ $clip->acls->except(Acl::PUBLIC())->pluck('name')->implode(', ') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
    @can('edit-clips',$clip)
        <div class="absolute w-full py-2.5 bottom-0 inset-x-0 text-white dark:text-white
                    text-xs text-right pr-2 pb-2 leading-4 min-pt-10">
            <a href="{{route('clips.edit', $clip)}}">
                <x-button class="bg-blue-500 hover:bg-blue-700">
                    <div class="flex items-center">
                        <div class="pr-5">
                            {{ __('common.actions.edit') }}
                        </div>
                        <div>
                            <x-heroicon-o-pencil class="w-4 h-4" />
                        </div>
                    </div>

                </x-button>
            </a>
        </div>
    @endcan
</div>
