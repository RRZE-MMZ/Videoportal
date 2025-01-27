@extends('layouts.frontend')

@section('content')
    <main class="sm:container sm:mx-auto sm:mt-10 sm:max-w-lg">
        <div class="flex">
            <div class="w-full">

                @if (session('resent'))
                    <div
                        class="mb-4 rounded-sm border border-t-8 border-green-600 bg-green-100 px-3 py-4 text-sm text-green-700"
                        role="alert">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                <section class="flex flex-col break-words bg-white sm:border-1 sm:rounded-md sm:shadow-xs sm:shadow-lg">
                    <header class="bg-gray-200 px-6 py-5 font-semibold text-gray-700 sm:rounded-t-md sm:px-8 sm:py-6">
                        {{ __('Verify Your Email Address') }}
                    </header>

                    <div class="w-full flex flex-wrap text-gray-700 leading-normal text-sm p-6
                            space-y-4 sm:text-base sm:space-y-6">
                        <p>
                            {{ __('Before proceeding, please check your email for a verification link.') }}
                        </p>

                        <p>
                            {{ __('If you did not receive the email') }},
                            <a
                                class="cursor-pointer text-blue-500 no-underline hover:text-blue-700 hover:underline"
                                onclick="event.preventDefault();
                                     document.getElementById('resend-verification-form').submit();"
                            >{{ __('click here to request another') }}</a>.
                        </p>

                        <form id="resend-verification-form" method="POST" action="{{ route('verification.resend') }}"
                              class="hidden">
                            @csrf
                        </form>
                    </div>

                </section>
            </div>
        </div>
    </main>
@endsection
