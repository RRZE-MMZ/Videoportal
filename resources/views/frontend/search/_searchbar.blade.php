<div class="flex justify-center rounded-sm">
    <form method="GET" action="{{ route('search') }}" class="w-full px-4 sm:px-0 lg:w-3/5">
        <div class="p-4">
            <div class="flex flex-col items-center rounded-sm bg-gray-50 dark:bg-slate-800 dark:border-white shadow-xl">
                <!-- Input Field -->
                <div class="flex w-full p-2 mt-4">
                    <div class="grow pr-10">
                        <input class="py-2 px-4 w-full leading-tight text-gray-700 dark:text-white
                                 rounded-full dark:placeholder-white dark:focus:border-white
                                 focus:outline-hidden dark:bg-slate-900 mb-4"
                               id="term"
                               type="text"
                               name="term"
                               placeholder="{{ __('homepage.Search form placeholder') }}">
                    </div>

                    <div class="flex-none">
                        <!-- Search Button -->
                        <div class="flex justify-center">
                            <button class="flex justify-center items-center p-2 w-10 h-10 text-white bg-gray-600
                                        rounded-full hover:bg-gray-500 focus:outline-hidden"
                                    type="submit">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Checkbox Group and Search Button -->
                <div class="flex flex-wrap justify-between pb-2 pl-2 w-full items-center">
                    <div class="flex flex-col lg:flex-row lg:space-x-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="search-series-checkbox"
                                   class="form-checkbox h-5 w-5 text-blue-600"
                                   name="series" checked />
                            <label for="search-series-checkbox" class="ml-2 text-black dark:text-white">
                                Series
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="search-clips-checkbox"
                                   class="form-checkbox h-5 w-5 text-blue-600"
                                   name="clips" checked />
                            <label for="search-clips-checkbox" class="ml-2 text-black dark:text-white">
                                Clips
                            </label>
                        </div>
                        @can('administrate-assistant-pages')
                            <div class="flex items-center">
                                <input type="checkbox" id="search-channels-checkbox"
                                       class="form-checkbox h-5 w-5 text-blue-600"
                                       name="channels" @checked(old('channels', )) />
                                <label for="search-channels-checkbox" class="ml-2 text-black dark:text-white">
                                    {{ trans_choice('common.menu.channel', 2) }}
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="search-podcasts-checkbox"
                                       class="form-checkbox h-5 w-5 text-blue-600"
                                       name="podcasts" @checked(old('podcasts', )) />
                                <label for="search-podcasts-checkbox" class="ml-2 text-black dark:text-white">
                                    Podcasts
                                </label>
                            </div>
                        @endcan
                    </div>
                </div>

                <!-- Error Message -->
                @error('term')
                <div class="flex items-center pb-2 justify-center">
                    <p class="text-xs text-red-500">{{ $message }}</p>
                </div>
                @enderror
            </div>
        </div>
    </form>
</div>
