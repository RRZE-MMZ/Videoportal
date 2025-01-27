@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            Collections Index
        </div>
        <div class="flex">
            <a href="{{route('collections.create')}}">
                <x-button class="flex items-center bg-blue-600 hover:bg-blue-700">
                    <div class="pr-2">
                        Create a new collection
                    </div>
                    <div>
                        <x-heroicon-o-plus-circle class="h-6 w-6" />
                    </div>
                </x-button>
            </a>
        </div>
    </div>
    @if ($collections->count() > 0 )
        <div class="flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                    <div class="mt-4 overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
                        <table class="min-w-full">
                            <thead class="border-b bg-white text-gray-900 dark:bg-gray-800 dark:text-white font-normal">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-sm">
                                    Position
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm ">
                                    Title
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm ">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm ">
                                    Clips
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm ">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($collections->sortBy('position') as $collection)
                                <tr class="@if ($collection->is_public) bg-gray-300 dark:bg-slate-800 @else bg-white @endif
                                    border-b transition duration-300  text-gray-900 ease-in-out hover:bg-gray-100
                                    text-sm leading-5 dark:text-white ">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        {{ $collection->position }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-light">
                                        {{ $collection->title }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-light">
                                        {{ str()->of($collection->description)->limit(50,' (...)') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-light">
                                        {{ $collection->clips()->count() }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-light">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('collections.edit', $collection)  }}">
                                                <x-button class="bg-blue-600 hover:bg-blue-700">
                                                    {{ __('common.actions.edit') }}
                                                </x-button>
                                            </a>
                                            <x-modals.delete
                                                :route="route('collections.destroy', $collection)"
                                                class="w-full justify-center"
                                            >
                                                <x-slot:title>
                                                    {{ __('collection.backend.delete.modal title',[
                                                    'collection_title'=>$collection->title
                                                    ]) }}
                                                </x-slot:title>
                                                <x-slot:body>
                                                    {{ __('presenter.backend.delete.modal body') }}
                                                </x-slot:body>
                                            </x-modals.delete>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
