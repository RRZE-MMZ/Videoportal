<div class="flex flex-col  font-normal">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="w-full max-w-lg lg:max-w-xs">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                        </div>
                        <input wire:model.live="search"
                               id="search"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5
                                            bg-white placeholder-gray-500 focus:outline-hidden focus:placeholder-gray-400
                                            focus:border-blue-300 focus:shadow-outline-blue sm:text-sm transition
                                            duration-150 ease-in-out"
                               placeholder="Search" type="search">
                    </div>
                </div>
            </div>

            <div class="mt-4 overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Image
                                </div>
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('description')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Description
                                </button>
                                <x-sort-icon
                                    field="description"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('file_name')"
                                        class="bg-gray-50 leading-4 font-medium dark:bg-gray-900 text-xs dark:text-white
                                               text-gray-500 uppercase tracking-wider"
                                >
                                    File name
                                </button>
                                <x-sort-icon
                                    field="file_name"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    File size
                                </div>
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('created_at')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Created at
                                </button>
                                <x-sort-icon
                                    field="created_at"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('updated_at')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                               text-gray-500 uppercase tracking-wider"
                                >
                                    Updated at
                                </button>
                                <x-sort-icon
                                    field="updated_at"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </div>
                            </div>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 ">
                    @forelse ($images as $image)
                        <tr class="text-sm leading-5  dark:text-white ">
                            <td class="w-1/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="shrink-0">
                                        <img class="h-20 rounded-[21px]"
                                             src="{{ URL::asset('/images/'.$image->file_name) }}"
                                             alt="{{ $image->description }}">
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium leading-5 ">
                                            {{ $image->description }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium leading-5 ">
                                            {{ $image->file_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium leading-5 ">
                                            {{ humanFileSizeFormat($image->file_size ?? 'null') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm leading-5 ">
                                    {{ $image->create_at?->diffForHumans() }}
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm leading-5 ">
                                    {{ $image->updated_at?->diffForHumans() }}
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 text-right text-sm font-medium leading-5 whitespace-no-wrap">
                                <div class="flex space-x-2">
                                    @can('administrate-admin-portal-pages')

                                        <a href="{{route('images.edit', $image)}}">
                                            <x-button type="button" class="bg-green-600 hover:bg-green-700">
                                                {{__('common.actions.edit')}}
                                            </x-button>
                                        </a>
                                        <livewire:delete-modal-window :model="$image"
                                                                      :wire:key="$image->id"
                                        />
                                    @else
                                        <a href="{{route('images.show', $image)}}">
                                            <x-button type="button" class="bg-green-600 hover:bg-green-700">
                                                {{__('common.actions.show')}}
                                            </x-button>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="dark:bg-gray-800 dark:text-white">
                            <td colspan="7" class="items-center w-full text-center">
                                <div class="text-2xl m-4 p-4 ">
                                    No images found
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $images->links() }}
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>

