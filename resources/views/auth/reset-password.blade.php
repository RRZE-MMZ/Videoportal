<x-guest-layout>
    <div x-data="setup()" x-init="$refs.loading.classList.add('hidden'); setColors(color);" :class="{ 'dark': isDark}">
        <div
                class="flex min-h-screen flex-col items-center bg-gray-300  dark:bg-slate-800 pt-6 sm:justify-center sm:pt-0">
            <div class="w-full max-w-sm rounded-md bg-gray px-4 py-6 space-y-6 dark:bg-darker">
                <h1 class="text-center text-xl dark:text-white font-semibold">Reset password</h1>
                <x-auth-validation-errors class="mb-4" :errors="$errors" />
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div>
                        <x-label for="email" :value="__('Email')" class="dark:text-white" />

                        <x-input id="email" class="mt-1 block w-full" type="email" name="email"
                                 :value="old('email', $request->email)" required autofocus />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-label for="password" :value="__('Password')" class="dark:text-white" />

                        <x-input id="password" class="mt-1 block w-full" type="password" name="password"
                                 required />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-label for="password_confirmation" :value="__('Confirm Password')" class="dark:text-white" />

                        <x-input id="password_confirmation" class="mt-1 block w-full"
                                 type="password"
                                 name="password_confirmation" required />
                    </div>

                    <div class="mt-4 flex items-center justify-end">
                        <button
                                type="submit"
                                class="w-full px-4 py-2 font-medium text-white transition-colors duration-200
                            rounded-md bg-blue-900 hover:bg-blue-500
                            focus:outline-none focus:ring-2 focus:ring-primary
                            focus:ring-offset-1 dark:focus:ring-offset-darker"
                        >
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
