<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link rel="icon" href="{{ URL::asset('/css/favicon.ico') }}" type="image/x-icon" />
    @vite('resources/css/app.css')
</head>
<body x-cloak
      x-data="{darkMode:  $persist(false), menuOpen: false}"
      :class="{'dark': darkMode === true }"
      class="antialiased">
<div id="app">
    <header class="fixed top-0 z-10 w-full bg-gray-100 dark:bg-sky-950 text-dark dark:text-gray-200">
        <div class="flex justify-between items-center">
            @include('layouts.frontend.top-navigation')
        </div>
    </header>
    <main class="lg:mx-auto sm:mx-4 max-h-full min-h-screen pt-16 lg:flex bg-gray-200 dark:bg-slate-900">
        <div class="w-full pb-10">
            @yield('content')
        </div>
    </main>

    <footer class="bg-gray-100 dark:bg-sky-950">
        <div class="flex flex-col md:flex-row sm:flex-row justify-between items-center p-4 mx-auto
        max-w-screen-xl text-dark dark:text-white">
            <!-- Copyright Text -->
            <div class="w-full md:w-auto sm:w-auto mb-2 sm:mb-0 text-center md:text-left sm:text-left
            whitespace-nowrap text-wrap sm:italic">
                Copyright &copy; {{ Illuminate\Support\Carbon::now()->year }} ECL-2.0 license
                [br. {{ getCurrentGitBranch().' / deployed at:'.getDeployDate() }}]
            </div>

            <!-- Bottom Navigation -->
            <div class="w-full md:w-auto sm:w-auto flex justify-center sm:justify-end">
                @include('layouts.frontend.bottom-navigation')
            </div>
        </div>
    </footer>

</div>
@vite('resources/js/app.js')
</body>
</html>
