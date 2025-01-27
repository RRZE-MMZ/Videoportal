@use(App\Enums\ApplicationStatus)
@use(App\Models\User)

@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            Series Index
        </div>
    </div>
    @if(auth()->user()->notifications->count() > 0)
        <form action="{{ route('user.notifications.delete') }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="mt-4 overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <x-heroicon-o-trash class="h-6 w-6" />
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                Notification type
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                Description
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                Status
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                Actions
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse(auth()->user()->notifications as $notification)
                        <tr class="@if(is_null($notification->read_at)) font-bold @endif ">
                            <td class="w-1/12 px-6 py-4 whitespace-no-wrap">
                                <input type="checkbox" name="selected_notifications[]"
                                       value="{{ $notification->id }}" />
                            </td>
                            <td class="w-2/12 px-6 whitespace-no-wrap">
                                @if($notification->type === 'App\Notifications\NewAdminPortalNotification')
                                    User application
                                @else
                                    {{ $notification->type }}
                                @endif
                            </td>
                            <td class="w-3/12 px-6 py-4 whitespace-no-wrap">
                                Requested from {{ $notification->data['username_applied_for_admin_portal'] }}
                            </td>
                            <td class="w-3/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex flex-col py-1 items-left">
                                    @if($notification->data['application_status'] === ApplicationStatus::COMPLETED())
                                        <div
                                            class="text-green-500">{{ $notification->data['application_status'] }}</div>
                                        <div class="pt-2 pl-2">[Application processed by {{
                                            User::search($notification->data['application_status_processed_by'])
                                                ->first()
                                                ->getFullNameAttribute()
                                                }}]
                                        </div>
                                    @else
                                        {{ $notification->data['application_status'] }}
                                    @endif
                                </div>
                            </td>
                            <td class="w-3/12 px-6 py-4 whitespace-no-wrap">
                                @if($notification->type === 'App\Notifications\NewAdminPortalNotification')
                                    <a href="{{ route('users.edit',
                                User::search($notification->data['username_applied_for_admin_portal'])->first()) }}"
                                       target="_blank"
                                    >
                                        <x-button type="button" class="bg-blue-600 hover:bg-blue-700">
                                            View Moderator
                                        </x-button>
                                    </a>
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2  border bg-red-500
                                                    hover:bg-red-700 border-transparent rounded-md font-medium text-base
                                                     text-white tracking-wider active:bg-white-900 focus:outline-hidden
                                                    focus:border-white-900 focus:ring-3 ring-gray-300 disabled:opacity-25
                                                    transition ease-in-out duration-150"
                                            formaction="{{ route('user.notifications.delete', [
                                                'selected_notifications' => [$notification->id]]) }}">
                                        Delete notification
                                    </button>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="w-full px-6 py-4 whitespace-no-wrap">
                                You have no notifications
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(auth()->user()->notifications->count() > 0)
                <div class="flex flex-col pt-10">
                    <div>
                        <x-button type="submit" class="bg-red-500 hover:bg-red-700">
                            Delete all selected notifications
                        </x-button>
                    </div>
                    @error('selected_notifications')
                    <div class="flex">
                        <p class="mt-2 w-full text-xs text-red-500 font-xl">
                            {{ $message }}</p>
                    </div>
                    @enderror
                </div>

            @endif
        </form>
    @else
        <div class="flex justify-center text-center">
            <div class="mt-4 w-full overflow-hidden border-gray-200 p-4 text-xl shadow-sm dark:text-white">
                You have no notifications
            </div>
        </div>
    @endif
@endsection
