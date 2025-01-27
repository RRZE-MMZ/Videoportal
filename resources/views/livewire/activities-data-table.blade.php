@php use Illuminate\Support\Str; @endphp
<div class="flex flex-col font-normal">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="w-full max-w-lg lg:max-w-xs">
                    <label for="search" class="sr-only">
                        {{ __('common.actions.search') }}
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                        </div>
                        <input wire:model.live="search"
                               id="search"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5
                                            bg-white placeholder-gray-500 focus:outline-hidden focus:placeholder-gray-400
                                            dark:placeholder-gray-800 dark:bg-gray-300 dark:text-gray-900
                                            focus:border-blue-300 focus:shadow-outline-blue sm:text-sm transition
                                            duration-150 ease-in-out"
                               placeholder="{{ __('common.actions.search') }}" type="search">
                    </div>
                </div>
                @if ($model === '')
                    <div class="relative flex items-start">
                        <div class="flex h-5 items-center">
                            <input wire:model.live="series" id="series" type="checkbox"
                                   class="h-4 w-4 text-indigo-600 transition duration-150 ease-in-out form-checkbox">
                        </div>
                        <div class="ml-3 text-sm leading-5">
                            <label for="series" class="font-medium text-gray-700">{{ __('common.menu.series') }}</label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4 overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th
                                class="px-2 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('id')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    ID
                                </button>
                                <x-sort-icon
                                        field="id"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('created_at')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white  leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.updated') }}
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
                                <button wire:click="sortBy('user_id')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.full name') }}
                                </button>
                                <x-sort-icon
                                        field="user_id"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('content_type')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.type') }}
                                </button>
                                <x-sort-icon
                                        field="content_type"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('change_message')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white  leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.message') }}
                                </button>
                                <x-sort-icon
                                        field="change_message"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('changes')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white  leading-4
                                        font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.changes') }}
                                </button>
                                <x-sort-icon
                                        field="changes"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($activities as $activity)
                        <tr class=" dark:bg-slate-800 text-xs ">
                            <td class="w-1/12 px-2 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-normal leading-5 text-gray-900 dark:text-white">
                                            {{ $activity->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-2 py-4 whitespace-no-wrap">
                                <div class="flex items-center text-center">
                                    <div class="ml-2">
                                        <div class="text-sm font-normal leading-5 text-gray-900 dark:text-white">
                                            {{ $activity->created_at }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center text-center">
                                    <div class="ml-1">
                                        <div class="text-sm font-normal leading-5 text-gray-900 dark:text-white">
                                            {{ $activity->user_real_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-2">
                                        <div class="text-sm font-normal leading-5 text-gray-900 dark:text-white">
                                            <a href="{{ '/admin/'.Str::plural($activity->content_type).'/'.$activity->object_id}}">
                                                {{ Str::plural($activity->content_type) }} -
                                                ID {{ $activity->object_id }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-3/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-2">
                                        <div class="text-sm font-normal leading-5 text-gray-900 dark:text-white">
                                            {{ $activity->change_message }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-4/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-2">
                                        <div class="text-sm font-normal leading-5 text-gray-900 dark:text-white">
                                            @if (!empty($activity->changes) )
                                                @if(isset($activity->changes['before']))
                                                    @foreach($activity->changes['before'] as $key=>$value)
                                                        <div class="w-full text-red-600">
                                                            {{ '--'. $key. '=>'.$value}}
                                                        </div>
                                                    @endforeach
                                                @endif
                                                @if(isset($activity->changes['after']))
                                                    @foreach($activity->changes['after'] as $key=>$value)
                                                        <div class="w-full text-green-600">
                                                            {{ '++'. $key. '=>'.$value}}
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @else
                                                {{ '-' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="dark:bg-gray-800 dark:text-white">
                            <td colspan="7" class="items-center w-full text-center">
                                <div class="text-2xl m-4 p-4 ">
                                    {{ __('common.no activities found') }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>
