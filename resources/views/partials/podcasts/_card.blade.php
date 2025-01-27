<div class="grid grid-cols-1 sm:grid-cols-3 bg-gray-50 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <!-- Image Section -->
    <div class="col-span-1 flex justify-center sm:justify-start p-4">
        <a href="{{ route('frontend.podcasts.show', $podcast) }}" class="block">
            <img class="w-32 sm:w-48 rounded-lg"
                 @if(!is_null($podcast->image_id))
                     src="{{ asset('images/'.$podcast->cover->file_name) }}"
                 @else
                     src="/podcast-files/covers/PodcastDefaultFAU.png"
                 @endif
                 alt="{{ $podcast->title }} {{ __('podcast.common.podcast cover') }}">
        </a>
    </div>

    <!-- Content Section -->
    <div class="col-span-2 flex flex-col justify-between p-4">
        <!-- Title -->
        <h3 class="text-lg sm:text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
            <a @if(str_contains(url()->current(), 'admin'))
                   href="{{ route('podcasts.edit', $podcast) }}">{{ $podcast->title }}
                @else
                    href="{{ route('frontend.podcasts.show', $podcast) }}">{{ $podcast->title }}
                @endif
            </a>
        </h3>

        <!-- Description -->
        <p class="text-sm sm:text-base font-light text-gray-800 dark:text-gray-300 mb-4">
            @if($podcast->description === '')
                <span class="italic">{{ __('common.no description available') }}</span>
            @else
                {{ Str::limit(removeHtmlElements($podcast->description), 250, ' (...)') }}
            @endif
        </p>

        <!-- Links and Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
            <!-- Social Links -->
            <ul class="flex space-x-4">
                <li>
                    <a href="{{ $podcast->website_url }}"
                       class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                        <x-iconoir-www class="w-5 sm:w-6 text-black dark:text-white" />
                    </a>
                </li>
                <li>
                    <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                        <x-iconoir-spotify class="w-5 sm:w-6 dark:text-white" />
                    </a>
                </li>
                <li>
                    <a href="#" class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                        <x-iconoir-apple-mac class="w-5 sm:w-6 text-black dark:text-white" />
                    </a>
                </li>
            </ul>

            <!-- Edit and Delete Actions -->
            @can('edit-podcast', $podcast)
                <div class="flex space-x-2">
                    <a href="{{ route('podcasts.edit', $podcast) }}">
                        <x-button class="bg-green-500 hover:bg-green-700 ">
                            {{ __('podcast.common.edit podcast') }}
                        </x-button>
                    </a>
                    @if(str_contains(url()->current(), 'admin'))
                        <x-modals.delete :route="route('podcasts.destroy', $podcast)">
                            <x-slot:title>
                                {{ __('podcast.backend.delete.modal title', ['podcast_title' => $podcast->title]) }}
                            </x-slot:title>
                            <x-slot:body>
                                {{ __('podcast.backend.delete.modal body') }}
                            </x-slot:body>
                        </x-modals.delete>
                    @endif
                </div>
            @endcan
        </div>
    </div>
</div>
