@use(App\Enums\Acl)
<div class="flex flex-col font-normal">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="w-full max-w-lg lg:max-w-xs">
                    <label for="search" class="sr-only">{{ __('common.actions.search') }}</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                        </div>
                        <input wire:model.live="search"
                               id="search"
                               class="block w-full pl-10 pr-3 py-2 my-2 border border-gray-300 rounded-md leading-5
                                            bg-white placeholder-gray-500 focus:outline-hidden focus:placeholder-gray-400
                                            focus:border-blue-300 focus:shadow-outline-blue sm:text-sm transition
                                            duration-150 ease-in-out"
                               placeholder="{{ __('common.actions.search') }}" type="search">
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="relative flex items-start pr-4 ">
                        <div class="flex h-5 items-center pr-4">
                            <input wire:model.live="userClips"
                                   id="user-clips"
                                   type="checkbox"
                                   class="h-4 w-4 text-indigo-600 transition duration-150 ease-in-out form-checkbox"
                            >
                            <div class="ml-3 text-sm leading-5">
                                <label for="user-clips"
                                       class=" text-gray-700 dark:text-white "
                                >
                                    {{ __('clip.backend.my clips') }}
                                </label>
                            </div>
                        </div>
                        <div class="flex h-5 items-center">
                            <input wire:model.live="withAssets"
                                   id="with-assets"
                                   type="checkbox"
                                   class="h-4 w-4 text-indigo-600 transition duration-150 ease-in-out form-checkbox">
                            <div class="ml-3 text-sm leading-5"
                            >
                                <label for="with-assets"
                                       class="text-gray-700 dark:text-white"
                                >
                                    {{ __('clip.backend.with video files') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="relative flex items-start">
                        <select wire:model.live="selectedSemesterID"
                                class="dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">
                                {{ __('series.backend.actions.select semester') }}
                            </option>
                            @foreach ($semestersList as $semester)
                                <option value="{{ $semester->id }}">
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
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
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.forms.title') }}
                                </button>
                                <x-sort-icon
                                        field="title"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc"
                                />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('series_id')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4
                                                    text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('common.menu.series') }}
                                </button>
                                <x-sort-icon
                                        field="series_id"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc"
                                />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('semester_id')"
                                        class="bg-gray-50 text-xs leading-4 dark:bg-gray-900 text-xs dark:text-white
                                                    text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('series.common.semester') }}
                                </button>
                                <x-sort-icon
                                        field="location"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('faculty')"
                                        class="bg-gray-50 text-xs leading-4 dark:bg-gray-900 text-xs dark:text-white
                                                    text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('series.common.access via') }}
                                </button>
                                <x-sort-icon
                                        field="faculty"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc"
                                />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('faculty')"
                                        class="bg-gray-50 text-xs leading-4 dark:bg-gray-900 dark:text-white
                                                    text-gray-500 uppercase tracking-wider"
                                >
                                    {{ trans_choice('common.menu.organization', 1) }}
                                </button>
                                <x-sort-icon
                                        field="faculty"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('faculty')"
                                        class="bg-gray-50 text-xs leading-4 dark:bg-gray-900 text-xs dark:text-white
                                                    text-gray-500 uppercase tracking-wider"
                                >
                                    {{ trans_choice('common.menu.presenter', 2) }}
                                </button>
                                <x-sort-icon
                                        field="faculty"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div
                                        class="bg-gray-50 text-xs leading-4 dark:bg-gray-900 text-xs dark:text-white
                                                    text-gray-500 uppercase tracking-wider"
                                >
                                    {{ __('series.common.actions') }}
                                </div>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 ">
                    @forelse ($clips as $clip)
                        <tr class="text-sm leading-5 text-gray-900 dark:text-white ">
                            <td class="w-4/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="h-12 w-24 shrink-0">
                                        <img class="h-12 w-24 "
                                             src="{{fetchClipPoster($clip->latestAsset()?->player_preview) }}" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $clip->title.' / ID:'.$clip->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            @if($clip->series_id > 0 && $clip->series->exists)
                                                @can('edit-clips', $clip)
                                                    <a href="{{ route('series.edit', $clip->series) }}">
                                                        {{ $clip->series->title }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('frontend.series.show', $clip->series->slug) }}">
                                                        {{ $clip->series->title }}
                                                    </a>
                                                @endcan
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $clip->semester->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            <div class="pr-2">
                                                @if(!$clip->acls->contains(Acl::PUBLIC))
                                                    @can('watch-video', $clip)
                                                        <x-heroicon-o-lock-open class="h-4 w-4 text-green-500" />
                                                        <span class="sr-only">Unlock clip</span>
                                                    @else
                                                        <x-heroicon-o-lock-closed class="h-4 w-4 text-red-700" />
                                                        <span class="sr-only">Lock clip</span>
                                                    @endcan
                                                @endif
                                            </div>
                                            {{ $clip->acls->pluck('name')->implode(', ') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{  $clip->organization->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            @if($clip->presenters->isNotEmpty())
                                                <div class="flex items-center">
                                                    <div class="flex pr-2 items-center">
                                                        <div class="pr-2">
                                                            <x-heroicon-o-user class="h-4" />
                                                        </div>
                                                        <div class="flex items-center align-middle">
                                                            {{ $clip->presenters
                                                                       ->map(function($presenter){
                                                                           return $presenter->getFullNameAttribute();
                                                                       })->implode(',') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 text-right text-sm  leading-5 whitespace-no-wrap">
                                <div class="flex space-x-2">
                                    <a href="{{route('frontend.clips.show',$clip)}}">
                                        <x-button type="button" class="bg-green-600 hover:bg-green-700">
                                            {{__('common.actions.show')}}
                                        </x-button>
                                    </a>
                                    @can('edit-clips', $clip)
                                        <a href="{{route('clips.edit',$clip)}}">
                                            <x-button type="button" class="bg-green-600 hover:bg-green-700">
                                                {{__('common.actions.edit')}}
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
                                    No clips found
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex pt-8">
                <a href="{{route('clips.create')}}">
                    <x-button class="flex items-center bg-blue-600 hover:bg-blue-700">
                        <div class="pr-2">
                            {{ __('common.heading.create new clip') }}
                        </div>
                        <div>
                            <x-heroicon-o-plus-circle class="h-6 w-6" />
                        </div>
                    </x-button>
                </a>
            </div>
            <div class="mt-8">
                {{ $clips->links() }}
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>
