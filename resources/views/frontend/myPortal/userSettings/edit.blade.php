@extends('layouts.myPortal')

@section('myPortalHeader')
    <div class="text-lg sm:text-xl dark:text-white">
        {{ __('myPortal.myPortal Settings') }}
    </div>
@endsection

@section('myPortalContent')
    <div class="flex px-4 sm:px-6 py-4">
        <form action="{{ route('frontend.userSettings.update') }}" method="POST" class="w-full">
            @csrf
            @method('PUT')

            <div class="my-6 sm:my-10 grid grid-cols-1 sm:grid-cols-6 gap-4 items-center">
                <div class="sm:col-span-2">
                    <label for="language" class="block py-2 font-bold text-gray-700 text-md dark:text-white">
                        {{ __('myPortal.Portal language') }}
                    </label>
                </div>
                <div class="w-full sm:col-span-4 max-w-md">
                    <select id="language" name="language"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5
                                   dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                                   dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option @selected($settings['language'] === 'en') value="en">
                            {{ __('common.language.English') }}
                        </option>
                        <option @selected($settings['language'] === 'de') value="de">
                            {{ __('common.language.German') }}
                        </option>
                    </select>
                </div>
            </div>

            <x-form.toggle-button :value="$settings['show_subscriptions_to_home_page']"
                                  label="Show subscriptions on homepage"
                                  field-name="show_subscriptions_to_home_page"
                                  :label-class="'sm:col-span-4'" />

            <x-button class="mt-8 sm:mt-10 bg-blue-600 hover:bg-blue-700 w-full sm:w-auto">
                {{ str(__('common.actions.update'))->ucfirst() }}
            </x-button>
        </form>
    </div>
@endsection
