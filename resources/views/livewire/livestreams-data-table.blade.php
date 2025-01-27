<div class="flex flex-col font-normal">
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
                                <button wire:click="sortBy('name')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Name
                                </button>
                                <x-sort-icon
                                    field="name"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('opencast_location_name')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Opencast Location
                                </button>
                                <x-sort-icon
                                    field="opencast_location_name"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('time_availability_start')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Stream start time
                                </button>
                                <x-sort-icon
                                    field="time_availability_start"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('time_availability_end')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Stream end time
                                </button>
                                <x-sort-icon
                                    field="time_availability_end"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('content_path')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    content_path
                                </button>
                                <x-sort-icon
                                    field="content_path"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('active')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Active
                                </button>
                                <x-sort-icon
                                    field="active"
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
                                    Clip Info
                                </div>
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

                    @foreach ($livestreams as $livestream)
                        <tr class="leading-5  text-gray-900  dark:text-white  font-normal">
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div>
                                            {{ $livestream->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div>
                                            {{ $livestream->opencast_location_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div>
                                            {{ $livestream->time_availability_start }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div>
                                            {{ $livestream->time_availability_end }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div>
                                            {{ $livestream->content_path }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div>
                                    @if ($livestream->active)
                                        <x-heroicon-o-check-badge class="h-6 w-6" />
                                    @else
                                        <x-heroicon-o-x-circle class="h-6 w-6" />
                                    @endif
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div>
                                    {{ $livestream->clip->title }}
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 text-right text-sm font-medium leading-5 whitespace-no-wrap">
                                <div class="flex space-x-2">
                                    <a href="{{route('livestreams.edit',$livestream)}}">
                                        <x-button type="button" class="bg-green-600 hover:bg-green-700">
                                            {{__('common.actions.edit')}}
                                        </x-button>
                                    </a>
                                    <x-modals.delete
                                        :route="route('livestreams.destroy', $livestream)"
                                        class="w-full justify-center"
                                    >
                                        <x-slot:title>
                                            {{ __('livestream.backend.delete.modal title',[
                                            'livestream_name'=>$livestream->name
                                            ]) }}
                                        </x-slot:title>
                                        <x-slot:body>
                                            {{ __('livestream.backend.delete.modal body') }}
                                        </x-slot:body>
                                    </x-modals.delete>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $livestreams->links() }}
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>

