<div>
    <div class="flex  items-center space-x-10">
        <div class="pb-4 dark:bg-gray-900">
            <label for="table-search" class="sr-only">Search</label>
            <div class="relative mt-1 pt-4 pl-4">
                <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                </div>
                <input
                        wire:model.live="search"
                        type="search"
                        id="search"
                        class="block pt-2 ps-4 text-md text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50
                   focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600
                   dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Search for {{$type}}"
                >
            </div>
        </div>
        @if($type==='clips')
            <div class="relative flex items-start pr-4 ">
                <div class="flex h-5 items-center pr-4">
                    <input wire:model.live="singleClips" id="user-series" type="checkbox" checked
                           class="h-4 w-4 text-indigo-600 transition duration-150 ease-in-out form-checkbox">
                    <div class="ml-3  leading-5">
                        <label for="admin"
                               class=" text-gray-700 dark:text-white"
                        >
                            Einzelne Clips (die nicht Teil einer Videoserie sind)
                        </label>
                    </div>
                </div>

            </div>
        @endif

    </div>
    <div class="grid xl:grid-cols-4 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-4 pt-8">
        @if($type === 'series' || $type === 'organization')
            @forelse ($objs as $obj)
                @include('backend.series._card',[
                        'series'=> $obj,
                        'route' => 'admin',
                        'actionButton'=> $actionButton,
                        ])
            @empty
                @if($actionButton === 'assignClip')
                    <div class="flex col-span-12 items-center justify-center text-3xl dark:text-white">
                        <div class="w-full">
                            {{ __('series.backend.no user series found') }}
                        </div>
                    </div>
                    <div class="pt-10">
                        <a href="{{ route('series.create') }}">
                            <x-button class="bg-green-500 hover:bg-green-700">
                                {{ __('common.forms.create series') }}
                            </x-button>
                        </a>
                    </div>

                @else
                    <p class="col-span-4 text-3xl dark:text-white italic items-center">
                        {!!  __('search.frontend.no series results found for search term', ['searchTerm' => $search])  !!}
                    </p>

                @endif
            @endforelse
        @else
            @forelse ($objs as $obj)
                @include('backend.clips._card',[
                        'clip'=> $obj,
                        'route' => 'admin'
                        ])
            @empty
                <p class="col-span-4 text-3xl dark:text-white italic items-center">
                    {!!  __('search.frontend.no clips results found for search term', ['searchTerm' => $search])  !!}
                </p>
            @endforelse
        @endif
    </div>

    <div class="mt-8">
        {{ $objs->links() }}
    </div>
</div>
