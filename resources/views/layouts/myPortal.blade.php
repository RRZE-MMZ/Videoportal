@extends('layouts.frontend')

@section('content')
    <main class="container mx-auto mt-6 sm:mt-8 md:mt-12 px-4 sm:px-6">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="pt-6 sm:pt-10 text-xl sm:text-2xl">
                @yield('myPortalHeader')
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-6 sm:pt-10">
            <!-- Main Content -->
            <div class="md:col-span-9 lg:col-span-10">
                @yield('myPortalContent')
            </div>

            <!-- Sidebar -->
            <div class="md:col-span-3 lg:col-span-2">
                @include('frontend.myPortal._sidebar')
            </div>
        </div>
    </main>
@endsection
