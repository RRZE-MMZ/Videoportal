<div class="flex flex-col  font-normal">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="w-full max-w-lg lg:max-w-xs">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1
                                                1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                      clip-rule="evenodd">
                                </path>
                            </svg>
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
                <div class="flex space-x-4">
                    <div class="relative flex items-start">
                        <div class="flex h-5 items-center">
                            <input wire:model.live="admin" id="admin" type="checkbox"
                                   class="h-4 w-4 text-indigo-600 transition duration-150 ease-in-out form-checkbox">
                        </div>
                        <div class="ml-3 text-sm leading-5">
                            <label for="admin"
                                   class="text-gray-700 dark:text-white"
                            >
                                Admins
                            </label>
                        </div>
                    </div>
                    <div class="relative flex items-start">
                        <div class="flex h-5 items-center">
                            <input wire:model.live="deleted" id="deleted" type="checkbox"
                                   class="h-4 w-4 text-indigo-600 transition duration-150 ease-in-out form-checkbox">
                        </div>
                        <div class="ml-3 text-sm leading-5">
                            <label for="admin"
                                   class="text-gray-700 dark:text-white"
                            >
                                Deleted users
                            </label>
                        </div>
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
                                <button wire:click="sortBy('first_name')"
                                        class=" dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                                >
                                    First Name
                                </button>
                                <x-sort-icon
                                        field="first_name"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('last_name')"
                                        class=" dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                                >
                                    Last Name
                                </button>
                                <x-sort-icon
                                        field="last_name"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('username')"
                                        class=" dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                                >
                                    Username
                                </button>
                                <x-sort-icon
                                        field="username"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                                class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('email')"
                                        class=" dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                                >
                                    Email
                                </button>
                                <x-sort-icon
                                        field="email"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                            >
                                Roles
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="dark:bg-gray-900 text-xs dark:text-white leading-4
                                        text-gray-500 uppercase tracking-wider"
                            >
                                Login type
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('logged_in_at')"
                                        class=" dark:bg-gray-900 text-xs dark:text-white leading-4
                                       uppercase tracking-wider"
                                >
                                    Logged in at
                                </button>
                                <x-sort-icon
                                        field="logged_in_at"
                                        :sortField="$sortField"
                                        :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="dark:bg-gray-900 text-xs dark:text-white leading-4
                                        uppercase tracking-wider"
                            >
                                Actions
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 ">
                    @foreach ($users as $user)
                        <tr class="text-sm leading-5 text-gray-900 dark:text-white ">
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 shrink-0">
                                        <img class="h-10 w-10 rounded-full"
                                             src="@if(!is_null($user->presenter))
                                             {{ $user->presenter->getImageUrl() }}
                                             @else https://www.gravatar.com/avatar/?d=mp&f=y>
                                        @endif" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $user->first_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $user->last_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $user->username }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="">{{ $user->email }}</div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                @foreach($user->roles as $role)
                                    <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    bg-green-100 text-green-800 ">
                                    {{ $role->name }}
                                    </span>
                                @endforeach

                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="">{{ $user->login_type }}</div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                {{ $user->logged_in_at?->diffForHumans() }}
                            </td>
                            <td class="w-2/12 px-6 py-4 text-right text-sm font-medium leading-5 whitespace-no-wrap">
                                <div class="flex space-x-2">
                                    @if(!$user->deleted_at)
                                        <a href="{{route('users.edit',$user)}}">
                                            <x-button class="bg-blue-500 hover:bg-blue-700"
                                                      wire:click="route('users.destroy', $user)">
                                                {{ __('common.actions.edit') }}
                                            </x-button>
                                        </a>
                                        @if(auth()->user()->id !== $user->id && $user->login_type === 'local')
                                            @can('administrate-superadmin-portal-pages')
                                                <x-modals.delete
                                                        :route="route('users.destroy', $user)"
                                                        class="w-full justify-center"
                                                >
                                                    <x-slot:title>
                                                        {{ __('user.backend.delete.modal title',[
                                                        'user_fullname'=>$user->getFullNameAttribute()
                                                        ]) }}
                                                    </x-slot:title>
                                                    <x-slot:body>
                                                        {{ __('user.backend.delete.modal body', [
                                                            'series_counter' => $user->series()->count(),
                                                            'clips_counter'  => $user->clips()->count(),
                                                        ]) }}
                                                    </x-slot:body>
                                                </x-modals.delete>
                                            @endcan
                                        @endif
                                    @else
                                        DELETED USER
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $users->links() }}
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>

