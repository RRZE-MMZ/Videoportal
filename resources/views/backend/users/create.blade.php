@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            Create new user
        </div>
    </div>
    <div class="flex px-2 py-2">
        <form action="{{ route('users.store') }}"
              method="POST"
              class="w-4/5">
            @csrf

            <div class="flex flex-col gap-6 space-y-4 pt-10">

                <x-form.input field-name="first_name"
                              input-type="text"
                              :value="old('first_name')"
                              label="First Name"
                              :full-col="true"
                              :required="true"
                />

                <x-form.input field-name="last_name"
                              input-type="text"
                              :value="old('last_name')"
                              label="Last Name"
                              :full-col="true"
                              :required="true"
                />

                <x-form.input field-name="username"
                              input-type="username"
                              :value="getValidLocalUsername()"
                              label="Username"
                              :read-only="true"
                              :full-col="false"
                              :required="true"
                />

                <x-form.input field-name="email"
                              input-type="email"
                              :value="old('email')"
                              label="Email"
                              :full-col="true"
                              :required="true"
                />

                <div class="col-span-7 w-4/5">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        Create local user
                    </x-button>
                    <a href="{{route('users.index')}}">
                        <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                            {{ __('common.actions.cancel') }}
                        </x-button>
                    </a>
                </div>
            </div>

        </form>
    </div>
@endsection
