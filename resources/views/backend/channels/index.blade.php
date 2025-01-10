@php use Illuminate\Support\Str; @endphp
@extends('layouts.backend')

@section('content')
    @if($channels->isEmpty())
        @include('backend.channels.activate._form')
    @else
        <div class="flex items-center border-b border-black pb-2 font-semibold font-2xl align-items-center
    dark:text-white dark:border-white">
            <div class="flex text-2xl">
                {{ __('channel.common.portal channels') }}
            </div>
        </div>

        <div class="grid gap-4 pt-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 font-normal
            dark:text-white">
            @foreach($channels as $channel)
                @include('partials.channels._card')
            @endforeach
        </div>
    @endif

@endsection
