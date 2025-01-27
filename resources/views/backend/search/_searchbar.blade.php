<div class="flex content-center justify-center">
    <form method="GET"
          action="{{ route('admin.search') }}"
          class="w-3/5">
        @csrf

        <div class="p-2">
            <div class="flex flex-col items-center rounded-full bg-white dark:bg-sky-950">
                <div class="flex w-full items-center rounded-full">
                    <input
                            class="ml-2 w-full px-4 py-2 text-gray-700 focus:outline-hidden placeholder-gray-500
                        placeholder-opacity-100 "
                            id="term"
                            type="text"
                            name="term"
                            placeholder="{{ __('search.backend.form placeholder') }}">

                    <div class="p-4">
                        <button class="flex justify-center items-center p-2 w-8 h-8 text-white bg-gray-600 dark:bg-white
                                            rounded-full hover:bg-gray-500 focus:outline-hidden"
                                type="search"
                        >
                            <svg class="h-6 w-6 dark:text-black"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg"
                            >
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </div>
                @error('term')
                <div class="items-center pb-2 justify-content-center">
                    <p class="text-xs text-red-500">{{ $message }}</p>
                </div>
                @enderror
            </div>
        </div>
    </form>
</div>
