@extends('layouts.frontend'))

@section('content')
    <main class="sm:container sm:mx-auto sm:mt-10 sm:max-w-lg">
        <div class="flex">
            <div class="w-full">

                @if (session('status'))
                    <div
                        class="bg-green-100 px-5 py-6 text-sm text-green-700 sm:mb-6 sm:rounded-sm sm:border sm:border-green-400"
                        role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <section class="flex flex-col break-words bg-white sm:border-1 sm:rounded-md sm:shadow-xs sm:shadow-lg">
                    <header class="bg-gray-200 px-6 py-5 font-semibold text-gray-700 sm:rounded-t-md sm:px-8 sm:py-6">
                        {{ __('Reset Password') }}
                    </header>

                    <form class="w-full px-6 space-y-6 sm:space-y-8 sm:px-10" method="POST"
                          action="{{ route('password.email') }}">
                        @csrf

                        <div class="flex flex-wrap">
                            <label for="email" class="mb-2 block text-sm font-bold text-gray-700 sm:mb-4">
                                {{ __('E-Mail Address') }}:
                            </label>

                            <input id="email" type="email"
                                   class="form-input w-full @error('email') border-red-500 @enderror" name="email"
                                   value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                            <p class="mt-4 text-xs italic text-red-500">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-center items-center space-y-6 pb-6 sm:pb-10
                                sm:space-y-0 sm:justify-between">
                            <button type="submit"
                                    class="w-full select-none font-bold whitespace-no-wrap p-3 rounded-lg text-base
                                leading-normal no-underline text-gray-100 bg-blue-500 hover:bg-blue-700
                                sm:w-auto sm:px-4 sm:order-1">
                                {{ __('Send Password Reset Link') }}
                            </button>

                            <p class="mt-4 text-xs text-blue-500 hover:text-blue-700 whitespace-no-wrap no-underline
                                    hover:underline sm:text-sm sm:order-0 sm:m-0">
                                <a class="text-blue-500 no-underline hover:text-blue-700" href="{{ route('login') }}">
                                    {{ __('Back to login') }}
                                </a>
                            </p>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>
@endsection
