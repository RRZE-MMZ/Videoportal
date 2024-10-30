@php use App\Enums\Acl; @endphp
@use(App\Models\Series)

<div class="flex flex-col space-y-4">

    <div class="flex flex-col content-center justify-center pt-4 sm:pt-6">
        @can('watch-video', $clip)
            <div class="w-full max-w-6xl mx-auto">
                <x-player class="w-full" :clip="$clip" :wowzaStatus="$wowzaStatus"
                          :default-video-url="$defaultVideoUrl" />
            </div>
            @if (count($alternativeVideoUrls) > 1)
                <div class="pb-5">
                    <div class="flex flex-wrap gap-2 pt-6 dark:text-white justify-center">
                        @foreach($alternativeVideoUrls as $type => $url)
                            <div>
                                <a href="{{ $url }}"
                                   class="video-link flex items-center px-3 py-1 sm:px-4 sm:py-2 bg-blue-800 border border-transparent rounded-md
                                    font-semibold text-xs sm:text-sm text-white uppercase tracking-widest
                                    hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900
                                    focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                                   title="{{ __('clip.frontend.show.' . $type . ' video stream') }}">
                                    @if($type === 'presenter')
                                        <x-iconoir-video-camera class="w-5 sm:w-6 h-5 sm:h-6" />
                                    @elseif($type === 'presentation')
                                        <x-iconoir-video-projector class="w-5 sm:w-6 h-5 sm:h-6" />
                                    @elseif($type === 'composite')
                                        <x-iconoir-view-columns-2 class="w-5 sm:w-6 h-5 sm:h-6" />
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center space-y-4 py-8 sm:py-16">
                @foreach($clip->acls->pluck('id') as $acl)
                    @if($acl == Acl::PORTAL())
                        <p class="dark:text-white text-lg sm:text-2xl text-center">{{ __('clip.frontend.this clip is exclusively accessible to logged-in users') }}</p>
                    @elseif($acl == Acl::LMS())
                        <p class="dark:text-white text-lg sm:text-2xl text-center">{{ __('clip.frontend.access to this clip is restricted to LMS course participants') }}</p>
                    @elseif($acl == Acl::PASSWORD())
                        <p class="dark:text-white text-lg sm:text-2xl text-center">{{ __('clip.frontend.this clip requires a password for access') }}</p>
                    @endif
                @endforeach
            </div>

            <div class="flex flex-wrap justify-center gap-4 px-4 sm:px-16">
                @foreach($clip->acls->pluck('id') as $acl)
                    @if($acl == Acl::PORTAL())
                        <a href="{{ route('login') }}">
                            <x-button class="flex items-center bg-blue-600 hover:bg-blue-700 text-xs sm:text-sm">
                                {{ __('auth.Login') }}
                                <x-heroicon-o-arrow-right class="w-4 sm:w-6 ml-2 sm:ml-4" />
                            </x-button>
                        </a>
                    @endif
                    @if($acl == Acl::LMS())
                        <a href="{{ $clip->series->lms_link }}">
                            <x-button class="flex items-center bg-blue-600 hover:bg-blue-700 text-xs sm:text-sm">
                                {{ __('clip.frontend.show.to LMS course') }}
                                <x-heroicon-o-arrow-right class="w-4 sm:w-6 ml-2 sm:ml-4" />
                            </x-button>
                        </a>
                    @endif
                @endforeach
            </div>
        @endcan
    </div>

    <div class="flex">
        <div x-data="{ open: false }" class="w-full">
            <div class="flex justify-left pt-4">
                <a href="#courseFeeds"
                   x-on:click="open = ! open"
                   class="flex items-center px-3 sm:px-4 py-1 sm:py-2 bg-blue-800 border border-transparent rounded-md
                    font-semibold text-xs sm:text-sm text-white uppercase tracking-widest
                    hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900
                    focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Feeds
                    <x-heroicon-o-rss class="ml-2 sm:ml-4 h-4 sm:h-5 w-4 sm:w-5 fill-white" />
                </a>
            </div>
            <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-0"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-10"
                 x-transition:leave-end="opacity-0 translate-y-0" class="w-full p-4 text-center sm:text-left">
                <ul class="space-y-2">
                    @foreach($assetsResolutions as $resolutionText)
                        <li>
                            <a href="{{ route('frontend.clips.feed', [$clip, $resolutionText]) }}"
                               class="underline dark:text-white text-sm sm:text-base">
                                {{ $resolutionText }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="border-b-2 border-gray-500 pt-6 sm:pt-8 pb-3 dark:text-white">
        <div class="flex flex-col lg:grid lg:grid-cols-6 gap-4 text-center lg:text-center">
            @if ($clip->series_id)
                <div class="flex items-center justify-start lg:justify-center space-x-2">
                    <x-heroicon-o-academic-cap class="h-5 w-5" />
                    <a href="{{ route('frontend.series.show', $clip->series) }}" class="underline">
                        {{ $clip->series->title }}
                    </a>
                </div>
            @endif

            <div class="flex items-center justify-start lg:justify-center space-x-2">
                <x-heroicon-o-user-group class="h-5 w-5" />
                <span>{{ $clip->presenters->pluck('full_name')->implode(', ') }}</span>
            </div>

            @if($clip->is_livestream)
                <div class="flex items-center justify-start lg:justify-center space-x-2">
                    <x-heroicon-o-clock class="h-5 w-5" />
                    <span>LIVESTREAM</span>
                </div>
            @else
                <div class="flex items-center justify-start lg:justify-center space-x-2">
                    <x-heroicon-o-clock class="h-5 w-5" />
                    <span>{{ $clip->assets()->first()->durationToHours() }} Min</span>
                </div>
            @endif

            <div class="flex items-center justify-start lg:justify-center space-x-2">
                <x-heroicon-o-calendar class="h-5 w-5" />
                <span>{{ $clip->created_at->format('Y-m-d') }}</span>
            </div>

            <div class="flex items-center justify-start lg:justify-center space-x-2">
                <x-heroicon-o-arrow-up-circle class="h-5 w-5" />
                <span>{{ $clip->assets->first()?->updated_at }}</span>
            </div>

            <div class="flex items-center justify-start lg:justify-center space-x-2">
                <x-heroicon-o-eye class="h-5 w-5" />
                <span>{{ __('clip.frontend.show.views', ['numViews' => $clip->views()]) }}</span>
            </div>
        </div>
    </div>


</div>
