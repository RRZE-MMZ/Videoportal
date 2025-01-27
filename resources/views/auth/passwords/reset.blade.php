@extends('layouts.frontend')

@section('content')
    <main class="sm:container sm:mx-auto sm:mt-10 sm:max-w-lg">
        <div class="flex">
            <div class="w-full">
                <section class="flex flex-col break-words bg-white sm:border-1 sm:rounded-md sm:shadow-xs sm:shadow-lg">

                    <header class="bg-gray-200 px-6 py-5 font-semibold text-gray-700 sm:rounded-t-md sm:px-8 sm:py-6">
                        {{ __('Reset Password') }}
                    </header>

                    <form class="w-full px-6 space-y-6 sm:space-y-8 sm:px-10" method="POST"
                          action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="flex flex-wrap">
                            <label for="email" class="mb-2 block text-sm font-bold text-gray-700 sm:mb-4">
                                {{ __('E-Mail Address') }}:
                            </label>

                            <input id="email" type="email"
                                   class="form-input w-full @error('email') border-red-500 @enderror" name="email"
                                   value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                            <p class="mt-4 text-xs italic text-red-500">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

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

                        <div class="flex flex-wrap">
                            <label for="password-confirm" class="mb-2 block text-sm font-bold text-gray-700 sm:mb-4">
                                {{ __('Confirm Password') }}:
                            </label>

                            <input id="password-confirm" type="password" class="w-full form-input"
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="flex flex-wrap pb-8 sm:pb-10">
                            <button type="submit"
                                    class="w-full select-none font-bold whitespace-no-wrap p-3 rounded-lg text-base leading-normal
                                    no-underline text-gray-100 bg-blue-500 hover:bg-blue-700 sm:py-4">
                                {{ __('Reset Password') }}
                            </button>
                        </div>
                    </form>

                </section>
            </div>
        </div>
    </main>
@endsection
