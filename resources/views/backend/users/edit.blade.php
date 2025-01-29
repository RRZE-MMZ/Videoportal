@use(App\Models\Role as ModelRole)
@use(App\Enums\ApplicationStatus)
@use(Carbon\Carbon)

@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal pb-2">
        <div class="flex w-full items-center">
            <span>
                Edit user
            </span>
            <span class="px-2 italic">
                {{ $user->getFullNameAttribute() }}
            </span>| UserID: {{ $user->id }}
        </div>
    </div>
    <div class="flex justify-center content-center  py-2 px-2">
        <form action="{{ route('users.update',$user) }}"
              method="POST"
              class="w-4/5">
            @csrf
            @method('PATCH')

            <div class="flex flex-col gap-6">

                <x-form.input field-name="username"
                              input-type="text"
                              :value="$user->username"
                              label="Username"
                              :disabled="($user->login_type !=='local')"
                              :full-col="true"
                              :read-only="true"
                />

                <x-form.input field-name="first_name"
                              input-type="text"
                              :value="$user->first_name"
                              label="First Name"
                              :disabled="($user->login_type !=='local')"
                              :full-col="true"
                              :required="true"
                />

                <x-form.input field-name="last_name"
                              input-type="text"
                              :value="$user->last_name"
                              label="Last Name"
                              disabled="{{($user->login_type !=='local')}}"
                              :full-col="true"
                              :required="true"
                />

                <x-form.input field-name="email"
                              input-type="email"
                              :value="$user->email"
                              label="Email"
                              disabled="{{($user->login_type !=='local')}}"
                              :full-col="true"
                              :required="true"
                />

                <x-form.select2-multiple field-name="roles"
                                         :model="$user"
                                         label="Roles"
                                         :items="ModelRole::all()"
                                         select-class="select2-tides" />

                <div class="col-span-7 mt-10 w-4/5 space-x-4">
                    <x-button class="bg-blue-600 hover:bg-blue700">
                        Update user
                    </x-button>
                    <a href="{{route('users.index')}}">
                        <x-button type="button" class="bg-gray-600 hover:bg-gray:700">
                            Back to users list
                        </x-button>
                    </a>
                </div>
            </div>
        </form>

        <div class="space-y-5 w-1/5 h-full pr-4">
            @include('backend.users.sidebar._channel')
            @include('backend.users.sidebar._presenter')
        </div>
    </div>

    @if(isset($user->settings->data['admin_portal_application_status']))
        @if($user->settings->data['admin_portal_application_status'] === ApplicationStatus::IN_PROGRESS())
            <div class="pt-10 mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
            >
                Applications
            </div>
            <div class="flex flex-row items-center pt-5">
                <div class="pr-10 text-lg font-normal dark:text-white">
                    User requested access to admin portal
                </div>
                <div>
                    <form action="{{route('admin.portal.application.grant')}}"
                          method="POST">
                        @csrf
                        <input type="text"
                               name="username"
                               value="{{ $user->username}}"
                               hidden
                        />

                        <x-button class="bg-green-700 hover:bg-green-700">
                            Grant user access to admin portal
                        </x-button>
                    </form>
                </div>
            </div>
        @else
            <div class="flex flex-row items-center pt-5 dark:text-white">
                <div class="pr-10 text-lg">
                    User admin portal application processed by
                    <span
                            class="italic"> {{ $user->settings->data['admin_portal_application_processed_by'] }} </span>
                    <span class="text-sm">
                    {{ Carbon::createFromFormat(
                    'Y-m-d H-i-s',$user->settings->data['admin_portal_application_processed_at']
                    )->diffForHumans()  }}
                </span>
                </div>
            </div>
        @endif
    @endif

    @if($userSeriesCounter > 0)
        <div class="flex border-b border-black text-lg flex-col dark:text-white dark:border-white font-normal
    py-4 my-4">
            <div class="flex w-full items-center">
                {{ $user->getFullNameAttribute() }} owned {{ $userSeriesCounter.' '.__('common.menu.series') }}
            </div>
        </div>

        @livewire('user-series-data-table',['user' => $user])
    @endif
    @if($userClipsCounter > 0)
        <div class="flex border-b border-black text-lg flex-col dark:text-white dark:border-white font-normal
    py-4 mt-4 pt-10">
            <div class="flex w-full items-center">
                {{ $user->getFullNameAttribute() }} owned clips
            </div>
        </div>

        @livewire('user-clips-data-table',['user' => $user])
    @endif
@endsection
