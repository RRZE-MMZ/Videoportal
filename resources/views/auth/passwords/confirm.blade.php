@extends('layouts.frontend')

@section('content')
    <main class="sm:container sm:mx-auto sm:mt-10 sm:max-w-lg">
        <div class="flex">
            <div class="w-full">
                <section class="flex flex-col break-words bg-white sm:border-1 sm:rounded-md sm:shadow-xs sm:shadow-lg">

                    <header class="bg-gray-200 px-6 py-5 font-semibold text-gray-700 sm:rounded-t-md sm:px-8 sm:py-6">
                        {{ __('Confirm Password') }}
                    </header>

                    <form class="w-full px-6 space-y-6 sm:space-y-8 sm:px-10"
                          method="POST"
                          action="{{ route('password.confirm') }}">
                        @csrf

                        <p class="leading-normal text-gray-500">
                            {{ __('Please confirm your password before continuing.') }}
                        </p>

                        <div class="flex flex-wrap">
                            <label for="password" class="mb-2 block text-sm font-bold text-gray-700 sm:mb-4">
                                {{ __('Password') }}:
                            </label>

                            <input id="password" type="password"
                                   class="form-input w-full @error('password') border-red-500 @enderror" name="password"
                                   required autocomplete="new-password">

                            @error('password')
                            <p class="mt-4 text-xs italic text-red-500">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-center items-center space-y-6 pb-6 sm:pb-10 sm:space-y-0
                                sm:justify-between">
                            <button type="submit"
                                    class="w-full select-none font-bold whitespace-no-wrap p-3 rounded-lg text-base leading-normal
                                no-underline text-gray-100 bg-blue-500 hover:bg-blue-700 sm:w-auto sm:px-4 sm:order-1">
                                {{ __('Confirm Password') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="mt-4 text-xs text-blue-500 hover:text-blue-700 whitespace-no-wrap no-underline
                                    hover:underline sm:text-sm sm:order-0 sm:m-0"
                                   href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </form>

                </section>
            </div>
        </div>
    </main>
@endsection
