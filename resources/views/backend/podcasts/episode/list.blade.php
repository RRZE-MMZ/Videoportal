<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <div class="pb-4 bg-white dark:bg-gray-900">
        <label for="table-search" class="sr-only">Search</label>
        <div class="relative mt-1 pt-4 pl-4">
            <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
            </div>
            <input type="text"
                   id="table-search"
                   class="block pt-2 ps-10 text-md text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50
                   focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600
                   dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   placeholder="{{ __('common.actions.search for episodes') }}"
            >
        </div>
    </div>
    <table class="w-full text-lg text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
            <th scope="col" class="px-6 py-3">
            </th>
            <th scope="col" class="px-6 py-3">
                {{ __('common.forms.title') }}
            </th>
            <th scope="col" class="px-6 py-3">
                {{ __('common.description') }}
            </th>
            <th scope="col" class="px-6 py-3">
                {{ trans_choice('common.metadata.host',2) }}
            </th>
            <th scope="col" class="px-6 py-3">
                {{ __('series.common.duration') }}
            </th>
            <th>
                {{ __('common.publish status') }}
            </th>
            <th scope="col" class="px-6 py-3">
                {{ __('series.common.actions') }}
            </th>
        </tr>
        </thead>
        <tbody>
        @forelse ($podcast->episodes->sortBy('episode_number') as $episode)
            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                <th scope="row" class="px-6 py-4  text-gray-900 whitespace-nowrap dark:text-white">
                    {{$episode->episode_number}}
                </th>
                <td class="px-6 py-4  text-gray-900 whitespace-nowrap dark:text-white">
                    <div class="flex items-center space-x-2">
                        <div>
                            <img
                                    @if(!is_null($episode->image_id))
                                        src="{{ asset('images/'.$episode->cover->file_name) }}"
                                    alt="{{$episode->title }} podcast cover"
                                    class="w-24"
                                    @elseif(!is_null($episode->podcast->image_id))
                                        src="{{ asset('images/'.$episode->podcast->cover->file_name) }}"
                                    alt="{{ $episode->podcast->title }} podcast cover"
                                    class="w-24"
                                    @else
                                        src="/podcast-files/covers/PodcastDefaultFAU.png" alt="Podcast Cover 3"
                                    @endif
                            />
                        </div>
                        <div>{{ $episode->title }}</div>
                    </div>

                </td>
                <td class="px-6 py-4  text-gray-900  dark:text-white">
                    <p class=" mb-2 text-black dark:text-white">
                        @if($episode->description==='')
                            <span class="italic">
                            No description available
                            </span>
                        @else
                            {{ Str::limit(removeHtmlElements($episode->description), 250, ' (...)') }}
                        @endif
                    </p>
                </td>
                <td class="px-6 py-4">
                    {{ $episode->getPrimaryPresenters(primary: false)
                                ->map(fn($presenter) => $presenter->full_name)
                                ->join(', ') }}
                </td>
                <td class="px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white italic">
                    {{ $episode->assets()->first()?->durationToHours() }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col space-y-2 ">
                        <div class="flex items-center align-middle">
                        <span class="pr-2 dark:text-white">
                            Portal
                        </span>
                            <span>
                            <x-heroicon-c-check-badge class="w-4 h-4 text-green-700 dark:text-green-500" />
                        </span>

                        </div>
                        <div class="flex items-center align-middle">
                            <span class="pr-2 dark:text-white">
                                Spotify
                            </span>
                            <span>
                                @if($episode->spotify_url)
                                    <x-heroicon-c-check-badge class="w-4 h-4  dark:text-white" />
                                @else
                                    <x-heroicon-c-x-circle class="w-4 h-4 text-red-700 dark:text-red-500" />
                                @endif
                            </span>

                        </div>
                        <div class="flex items-center align-middle">
                                <span class="pr-2 dark:text-white">
                                    Apple Podcasts
                                </span>
                            <span>
                                      @if($episode->apple_url)
                                    <x-heroicon-c-check-badge class="w-4 h-4  dark:text-white" />
                                @else
                                    <x-heroicon-c-x-circle class="w-4 h-4  text-red-700 dark:text-red-500" />
                                @endif
                                </span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <a href="{{route('podcasts.episodes.edit',[$episode->podcast, $episode])}}">
                        <x-button type="button" class="bg-green-600 hover:bg-green-700">
                            {{__('common.actions.edit')}}
                        </x-button>
                    </a>
                </td>
            </tr>
        @empty
            <div>{{ __('podcast.common.no podcast episodes found') }}</div>
        @endforelse
        </tbody>
    </table>
</div>
