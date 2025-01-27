<x-guest-layout>
    <div x-data="setup()" x-init="$refs.loading.classList.add('hidden'); setColors(color);" :class="{ 'dark': isDark}">
        <div
                class="flex min-h-screen flex-col items-center bg-gray-300  dark:bg-slate-800 pt-6 sm:justify-center sm:pt-0">
            <div class="w-full max-w-sm rounded-md bg-gray px-4 py-6 space-y-6 dark:bg-darker">
                <h1 class="text-center text-xl dark:text-white font-semibold">Login</h1>
                @if(!is_null($saml2TenantUUID))
                    <a
                            href="{{route('saml.login', $saml2TenantUUID)}}"
                            class="flex items-center justify-center rounded-md bg-black dark:text-white
                        px-4 py-2 text-white transition-all duration-200 space-x-2
                        hover:bg-opacity-80 focus:outline-hidden focus:ring-2 focus:ring-black
                         focus:ring-offset-1 dark:focus:ring-offset-darker"
                    >
                        <span> Login with WebSSO </span>
                    </a>

                    <!-- Or -->
                    <div class="flex flex-nowrap items-center justify-center space-x-2">
                        <span class="h-px w-20 bg-gray-300"></span>
                        <span class="dark:text-white">OR</span>
                        <span class="h-px w-20 bg-gray-300"></span>
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <input class="w-full rounded-md border px-4 py-2 focus:ring-primary-100 focus:outline-hidden
                        focus:ring-3 dark:bg-darker dark:border-gray-700 dark:focus:ring-primary-darker"
                           type="text"
                           name="username"
                           placeholder="Username"
                           required
                    />
                    <input class="w-full rounded-md border px-4 py-2 focus:ring-primary-100 focus:outline-hidden
                        focus:ring-3 dark:bg-darker dark:border-gray-700 dark:focus:ring-primary-darker"
                           type="password"
                           name="password"
                           placeholder="Password"
                           required
                    />
                    <div class="flex justify-between py-5">
                        <div>
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox"
                                       class="rounded-sm border-gray-300 text-indigo-600 shadow-xs focus:border-indigo-300 focus:ring-3 focus:ring-indigo-200 focus:ring-opacity-50"
                                       name="remember">
                                <span class="ml-2 text-sm text-gray-600 dark:text-white">{{ __('Remember me') }}</span>
                            </label>
                        </div>
                        <div>
                            <x-theme-toogle />
                        </div>
                    </div>

                    <div>
                        <button
                                type="submit"
                                class="w-full px-4 py-2 font-medium text-white transition-colors duration-200
                            rounded-md bg-blue-900 hover:bg-blue-500
                            focus:outline-hidden focus:ring-2 focus:ring-primary
                            focus:ring-offset-1 dark:focus:ring-offset-darker"
                        >
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
