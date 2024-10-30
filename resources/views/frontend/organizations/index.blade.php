@extends('layouts.frontend')

@section('content')
    <div class="w-full md:container py-6 sm:py-10 px-4 sm:px-6 mx-auto">
        @include('frontend.search._searchbar')
    </div>
    <main class="container mx-auto mt-4 sm:mt-6 md:mt-8 h-auto">
        <div class="flex flex-col items-center text-center">
            <h2 class="text-xl sm:text-2xl font-bold dark:text-white">
                {{ __('organization.index.Organization index') }}
            </h2>
        </div>

        <ul class="flex flex-col space-y-4 pt-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($organizations as $organization)
                    <a href="{{ route('frontend.organizations.show', $organization) }}"
                       class="m-2 rounded-lg border-2 border-solid border-black dark:border-white p-4 dark:hover:bg-slate-500 hover:bg-blue-200 h-full flex items-center justify-center">
                        <div class="text-center">
                            <h3 class="font-semibold text-base sm:text-lg dark:text-white">
                                {{ $organization->name }}
                            </h3>
                        </div>
                    </a>
                @empty
                    <li class="my-2 w-full rounded bg-white p-4 text-center dark:text-white">
                        {{ __('organization.index.No organizations found') }}
                    </li>
                @endforelse
            </div>
        </ul>
    </main>
@endsection
