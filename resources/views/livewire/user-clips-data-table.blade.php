@php use App\Enums\Acl; @endphp
<div class="flex flex-col font-normal" x-data="{ selectedClips: [], toggleAll: false }">
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
                                            dark:placeholder-gray-800 dark:bg-white dark:text-gray-900
                                            focus:border-blue-300 focus:shadow-outline-blue sm:text-sm transition
                                            duration-150 ease-in-out"
                               placeholder="{{ __('common.actions.search') }}" type="search">
                    </div>
                </div>
            </div>
            <div class="mt-4 overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox"
                                   @click="toggleAll = !toggleAll; selectedClips = toggleAll ? @json($clips->pluck('id')) : []"
                                   x-bind:checked="toggleAll">
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div class="bg-gray-50 text-xs leading-4  dark:bg-gray-800 dark:text-white
                                            text-gray-500 uppercase tracking-wider"
                                >
                                    Title
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div class="bg-gray-50 text-xs leading-4  dark:bg-gray-800 dark:text-white
                                            text-gray-500 uppercase tracking-wider"
                                >
                                    Semester
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div class="bg-gray-50 text-xs leading-4  dark:bg-gray-800 dark:text-white
                                            text-gray-500 uppercase tracking-wider"
                                >
                                    Acl
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div class="bg-gray-50 text-xs leading-4  dark:bg-gray-800 dark:text-white
                                            text-gray-500 uppercase tracking-wider"
                                >
                                    Organization
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div class="bg-gray-50 text-xs leading-4  dark:bg-gray-800 dark:text-white
                                            text-gray-500 uppercase tracking-wider"
                                >
                                    Presenters
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div class="bg-gray-50 text-xs leading-4  dark:bg-gray-800 dark:text-white
                                            text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </div>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody
                            class="bg-white divide-y divide-gray-200 dark:bg-slate-800"
                    >
                    @forelse ($clips as $clip)
                        <tr class="text-lg leading-5 text-gray-900 dark:text-white ">
                            <td class="pl-4 pr-2 mx-2">
                                <div>
                                    <input type="checkbox"
                                           :checked="selectedClips.includes({{ $clip->id }})"
                                           @change="if (event.target.checked) selectedClips = [...selectedClips, {{ $clip->id }}]; else selectedClips = selectedClips.filter(id => id !== {{ $clip->id }})">
                                </div>
                            </td>
                            <td class="w-4/12 px-6 py-4 whitespace-no-wrap  ">
                                <div class="flex items-center">
                                    <div class="w-36 h-auto shrink-0">
                                        <img class="w-36 h-auto"
                                             src="{{ ($clip)
                                            ? fetchClipPoster($clip->latestAsset()?->player_preview)
                                            : "/images/generic_clip_poster_image.png" }}"
                                             alt="">
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
                                            {{ $clip->semester->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    @if($clip->acls->isNotEmpty() )
                                        <div class="flex items-center pt-2 justify-content-between">
                                            <div class="pr-2">
                                                @if(!$clip->acls->contains(Acl::PUBLIC))
                                                    @can('watch-video', $clip)
                                                        <x-heroicon-o-lock-open
                                                                class="h-4 w-4 text-green-500 dark:text-white" />
                                                        <span class="sr-only">{{ __('common.unlocked') }}</span>
                                                    @else
                                                        <x-heroicon-o-lock-closed
                                                                class="h-4 w-4 text-red-700 dark:text-white dark:bg-gray-50" />
                                                        <span class="sr-only">{{ __('common.locked') }}</span>
                                                    @endcan
                                                @endif
                                            </div>
                                            <div class="text-stm">
                                                <p class="italic text-gray-900 dark:text-white">
                                                    {{ $clip->acls->pluck('name')->implode(', ') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{  $clip->organization?->name }}
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
                            <td class="w-2/12 px-6 py-4 text-right leading-5 whitespace-no-wrap">
                                <div class="flex">
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
                                    No series found
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="flex w-full">
                    <div class="pt-10 w-full">
                        <form method="POST" action="{{ route('users.revokeMultipleClipsOwnerShip', $user) }}">
                            @csrf
                            <!-- Hidden input to store selected clip IDs -->
                            <input type="hidden" name="clip_ids" x-bind:value="JSON.stringify(selectedClips)">
                            <div class="flex w-1/3 items-center gap-x-4 pl-4">
                                <!-- Dropdown -->
                                <div class="w-full">
                                    <label>
                                        @livewire('search-users-dropdown')
                                    </label>
                                    <!-- Hidden Input to Store Selected User ID -->
                                    <input type="hidden" name="userID" value="{{ $selectedUserId }}">
                                </div>
                                <!-- Button -->
                                <div class="flex-shrink-0">
                                    <x-button
                                            class="bg-teal-500 whitespace-nowrap px-6 py-2"
                                            x-bind:disabled="selectedClips.length === 0"
                                            @click="alert('Are you sure you want to transfer ownership of ' + selectedClips.length + ' clips?')"
                                    >
                                        Transfer ownership to another user
                                        <span x-text="selectedClips.length" class="px-2"></span> clip(s)
                                    </x-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="py-4">
                    {{ $clips->links() }}
                </div>
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>
