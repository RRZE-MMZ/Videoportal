<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
    @trixassets
</head>
<body id="app"
      x-cloak
      x-data="{darkMode:  $persist(false)}"
      :class="{'dark': darkMode === true }"
      class="antialiased">
<div class="flex bg-gray-100">
    <aside class="relative hidden  w-[20rem] shadow-xl bg-sidebar dark:bg-sky-950 sm:block items-center">
        <div class="flex flex-col p-6 text-center align-center">
            <div class="pb-2">
                <a href="{{route('home')}}"
                   class="text-2xl font-semibold text-white hover:text-gray-300">
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>
            @env('local')
                <div class="flex text-white dark:text-white italic items-center place-content-center">
                    <div>
                        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                             xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                             viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                  d="M12.006 2a9.847 9.847 0 0 0-6.484 2.44 10.32 10.32 0 0 0-3.393
                                  6.17 10.48 10.48 0 0 0 1.317 6.955 10.045 10.045 0 0
                                  0 5.4 4.418c.504.095.683-.223.683-.494
                                  0-.245-.01-1.052-.014-1.908-2.78.62-3.366-1.21-3.366-1.21a2.711 2.711
                                  0 0
                                  0-1.11-1.5c-.907-.637.07-.621.07-.621.317.044.62.163.885.346.266.183.487.426.647.71.135.253.318.476.538.655a2.079
                                  2.079 0 0 0
                                  2.37.196c.045-.52.27-1.006.635-1.37-2.219-.259-4.554-1.138-4.554-5.07a4.022
                                  4.022 0 0 1 1.031-2.75 3.77 3.77 0 0 1 .096-2.713s.839-.275 2.749 1.05a9.26
                                  9.26 0 0 1 5.004 0c1.906-1.325 2.74-1.05 2.74-1.05.37.858.406 1.828.101
                                  2.713a4.017 4.017 0 0 1 1.029 2.75c0 3.939-2.339 4.805-4.564 5.058a2.471
                                   2.471 0 0 1 .679 1.897c0 1.372-.012 2.477-.012 2.814 0 .272.18.592.687.492a10.05
                                   10.05 0 0 0 5.388-4.421 10.473 10.473 0 0 0 1.313-6.948 10.32 10.32 0 0
                                   0-3.39-6.165A9.847 9.847 0 0 0 12.007 2Z"
                                  clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="pl-2">
                        {{getCurrentGitBranch()}}
                    </div>
                </div>

            @endenv

        </div>
        <nav class="pt-3 text-base font-semibold text-white text-center items-center ">
            @include('backend.dashboard._sidebar-navigation')
        </nav>
        <a href="#"
           class="absolute bottom-0 flex w-full items-center justify-center py-4 text-white upgrade-btn active-nav-link">
            <x-heroicon-o-chevron-double-left class="h-6 w-6" />
        </a>
    </aside>

    <div class="relative flex h-screen w-full flex-col overflow-y-hidden">
        <!-- Desktop Header -->
        <header class="flex w-full items-center justify-end bg-white px-6 sm:flex dark:bg-sky-950">
            <div class="w-1/2">
                @include('backend.search._searchbar')
            </div>
            <div>
                <div class="flex px-2">
                    <x-theme-toogle />
                </div>
            </div>
            <div x-data="{ isOpen: false }" class="flex relative justify-end dark:bg-slate-800 z-10">
                @if(!is_null(auth()->user()->presenter))
                    <img @click="isOpen = !isOpen" class="h-10 w-10 rounded-full"
                         src="{{ auth()->user()->presenter->getImageUrl() }}"
                         alt="{{ auth()->user()->getFullNameAttribute() }} image">
                @else
                    <x-heroicon-o-user @click="isOpen = !isOpen"
                                       class="relative w-8 h-8 rounded-full overflow-hidden border-2 border-gray-400
                                            hover:border-gray-300 focus:border-gray-300
                                            focus:outline-none dark:text-white"
                    />
                @endif

                <button x-show="isOpen"
                        @click="isOpen = false"
                        class="fixed inset-0 cursor-default">
                </button>
                <div x-show="isOpen"
                     class="absolute mt-12 w-48 items-center rounded-lg bg-white
                     dark:bg-sky-950 py-2 align-middle shadow-lg dark:text-white font-normal">
                    <a href="#" class="block px-4 py-2 hover:text-gray-400">
                        {{ __('common.menu.user settings') }}
                    </a>
                    <a href="{{ route('user.notifications') }}" class="block px-4 py-2 hover:text-gray-400">
                        {{ __('common.menu.notifications') }}
                        @if (($counter = auth()->user()->unreadNotifications->count()) > 0)
                            <span
                                    class="ml-1 rounded-full bg-white text-sm text-green-700 p-1.5"> {{ $counter }}</span>
                        @endif
                    </a>
                    <a href="{{ route('logout') }}"
                       class="block px-4 py-2 hover:text-gray-400"
                       onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">{{ __('auth.Logout') }}</a>
                    <form id="logout-form"
                          action="{{ route('logout') }}"
                          method="POST"
                          class="hidden">
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </header>

        <!-- Mobile Header & Nav -->
        <header x-data="{ isOpen: false }" class="w-full px-6 py-5 bg-sidebar sm:hidden">
            {{--            <div class="flex items-center justify-between">--}}
            {{--                <a href="index.html" class="text-3xl font-semibold uppercase text-white hover:text-gray-300">Admin</a>--}}
            {{--                <button @click="isOpen = !isOpen" class="text-3xl text-white focus:outline-none">--}}
            {{--                    <svg xmlns="http://www.w3.org/2000/svg"--}}
            {{--                         fill="none"--}}
            {{--                         viewBox="0 0 24 24"--}}
            {{--                         stroke-width="1.5"--}}
            {{--                         stroke="currentColor"--}}
            {{--                         class="h-6 w-6"--}}
            {{--                         x-show="!isOpen">--}}
            {{--                        <path stroke-linecap="round" stroke-linejoin="round"--}}
            {{--                              d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>--}}
            {{--                    </svg>--}}
            {{--                    <i x-show="isOpen" class="fas fa-times"></i>--}}
            {{--                </button>--}}
            {{--            </div>--}}

            {{--            <!-- Dropdown Nav -->--}}
            {{--            <nav :class="isOpen ? 'flex': 'hidden'" class="flex flex-col pt-4">--}}
            {{--                <a href="index.html" class="flex items-center py-2 pl-4 text-white active-nav-link nav-item">--}}
            {{--                    <i class="mr-3 fas fa-tachometer-alt"></i>--}}
            {{--                    Dashboard--}}
            {{--                </a>--}}
            {{--                <a href="blank.html"--}}
            {{--                   class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-sticky-note"></i>--}}
            {{--                    Blank Page--}}
            {{--                </a>--}}
            {{--                <a href="tables.html"--}}
            {{--                   class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-table"></i>--}}
            {{--                    Tables--}}
            {{--                </a>--}}
            {{--                <a href="forms.html"--}}
            {{--                   class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-align-left"></i>--}}
            {{--                    Forms--}}
            {{--                </a>--}}
            {{--                <a href="tabs.html"--}}
            {{--                   class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-tablet-alt"></i>--}}
            {{--                    Tabbed Content--}}
            {{--                </a>--}}
            {{--                <a href="calendar.html"--}}
            {{--                   class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-calendar"></i>--}}
            {{--                    Calendar--}}
            {{--                </a>--}}
            {{--                <a href="#" class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-cogs"></i>--}}
            {{--                    Support--}}
            {{--                </a>--}}
            {{--                <a href="#" class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-user"></i>--}}
            {{--                    My Account--}}
            {{--                </a>--}}
            {{--                <a href="#" class="flex items-center py-2 pl-4 text-white opacity-75 nav-item hover:opacity-100">--}}
            {{--                    <i class="mr-3 fas fa-sign-out-alt"></i>--}}
            {{--                    Sign Out--}}
            {{--                </a>--}}
            {{--                <button--}}
            {{--                    class="mt-3 flex w-full items-center justify-center rounded-lg bg-white py-2 font-semibold shadow-lg cta-btn hover:bg-gray-300 hover:shadow-xl">--}}
            {{--                    <i class="mr-3 fas fa-arrow-circle-up"></i> Upgrade to Pro!--}}
            {{--                </button>--}}
            {{--            </nav>--}}
            <!-- <button class="mt-5 flex w-full items-center justify-center rounded-tr-lg rounded-br-lg rounded-bl-lg bg-white py-2 font-semibold shadow-lg cta-btn hover:bg-gray-300 hover:shadow-xl">
                <i class="mr-3 fas fa-plus"></i> New Report
            </button> -->
        </header>

        <div class="flex h-screen w-full flex-col overflow-x-hidden">
            <main class="w-full flex-grow px-3 py-2 font-light bg-gray-100 dark:bg-gray-900">
                <div class="pb-6 text-3xl text-black">
                    @if(Session::has('flashMessage'))
                        <x-alerts.flash-alert :message="Session::get('flashMessage', 'default')" />
                    @endif
                </div>
                <div>
                    @yield('content')
                </div>
            </main>
            <footer class="w-full bg-white p-4 text-center dark:bg-sky-950 dark:text-white">
                Copyright @ {{ Illuminate\Support\Carbon::now()->year }} ECL-2.0 license
                [br. {{ getCurrentGitBranch().' / deployed at:'.getDeployDate() }}]
            </footer>
        </div>
    </div>
</div>
<!-- Scripts -->
@livewireScriptConfig
</body>
</html>



