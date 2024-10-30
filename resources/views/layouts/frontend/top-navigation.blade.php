<nav class="container mx-auto px-4 py-2 flex flex-col md:flex-row md:justify-between md:items-center">
    <!-- Branding / Logo -->
    <div class="flex justify-between items-center w-full md:w-auto mb-2 md:mb-0">
        <a href="{{ url('/') }}" class="font-bold no-underline">
            <div class="flex flex-col">
                <span class="text-2xl">
                    {{ config('app.name', 'Laravel') }}
                </span>
                <span class="text-sm">
                    [develop/unstable]
                </span>
            </div>
        </a>

        <!-- Hamburger Button (for mobile) -->
        <button @click="menuOpen = !menuOpen" class="block md:hidden text-gray-700 dark:text-white">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>
    </div>

    <!-- Navigation Links and Options -->
    <div :class="{'block': menuOpen, 'hidden': !menuOpen}"
         class="w-full md:flex md:justify-center md:space-x-8 md:items-center hidden mt-4 md:mt-0">
        <!-- Navigation Links -->
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-6 font-semibold text-lg">
            <a href="{{ route('frontend.channels.index') }}" class="block py-2 md:py-0">
                {{ trans_choice('common.menu.channel', 2) }}
            </a>
            <a href="{{ route('frontend.series.index') }}" class="block py-2 md:py-0">
                Series
            </a>
            <a href="{{ route('frontend.clips.index') }}" class="block py-2 md:py-0">
                {{ trans_choice('common.menu.clip', 2) }}
            </a>
            <a href="{{ route('frontend.podcasts.index') }}" class="block py-2 md:py-0">
                {{ trans_choice('common.menu.podcast', 2) }}
            </a>
            <a href="{{ route('frontend.organizations.index') }}" class="block py-2 md:py-0">
                {{ trans_choice('common.menu.organization', 2) }}
            </a>
            <a href="{{ route('frontend.livestreams.index') }}" class="block py-2 md:py-0">
                {{ __('common.menu.live now') }}
            </a>
            <a href="{{ route('frontend.faq') }}" class="block py-2 md:py-0">
                {{ trans_choice('common.menu.faq', 2) }}
            </a>
        </div>

        <!-- Auth Links & Additional Options -->
        <div class="flex flex-col space-y-4 mt-4 md:mt-0 md:flex-row md:space-y-0 md:space-x-6 items-start md:items-center">
            @guest
                <a href="{{ route('login') }}" class="block py-2 md:py-0">
                    {{ __('auth.Login') }}
                </a>
            @else
                <span class="block py-2 md:py-0">Hi, {{ Auth::user()->getFullNameAttribute() }}</span>
                <a href="{{ route('frontend.userSettings.edit') }}" class="block py-2 md:py-0">
                    {{ __('common.menu.myPortal', ['appName' => str(config('app.name'))->ucfirst() ]) }}
                </a>
                @if(!str_contains(url()->current(), 'admin') && auth()->user()->can('access-dashboard'))
                    <a href="/admin/dashboard" class="block py-2 md:py-0">Dashboard</a>
                @endif
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="block py-2 md:py-0">
                    {{ __('auth.Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    {{ csrf_field() }}
                </form>
            @endguest

            <!-- Dark Mode Toggle -->
            <div class="flex px-2 mt-4 md:mt-0">
                <button @click="darkMode = !darkMode" :class="{'bg-gray-800': darkMode, 'bg-gray-300': !darkMode}"
                        class="w-10 h-5 rounded-full flex items-center justify-between p-1 transition-colors duration-300">
                    <div :class="{'translate-x-5': darkMode, 'translate-x-0': !darkMode}"
                         class="w-4 h-4 bg-white rounded-full transform transition-transform duration-300"></div>
                </button>
            </div>

            <!-- Language Toggle -->
            <span class="block py-2 md:py-0">
                <a href="/set_lang/en" class="{{ (session('locale') === 'en') ? 'underline' : '' }}">EN</a> |
                <a href="/set_lang/de" class="{{ (session('locale') === 'de') ? 'underline' : '' }}">DE</a>
            </span>
        </div>
    </div>
</nav>
