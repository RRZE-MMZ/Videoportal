@php use Illuminate\Support\Carbon; @endphp
@extends('layouts.backend')
@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal">
        <div class="font-semibold ">
            @if($obj['type']==='clip')
                {{ __('statistic.backend.statistic for clip:', [
                                                                'clip_title' => $obj['info']->title,
                                                                'clip_id' => $obj['info']->id
                                                                ]) }}
            @else
                {{ __('statistic.backend.statistic for series:', [
                                                                'series_title' => $obj['info']->title,
                                                                'series_id' => $obj['info']->id
                                                                ]) }}
            @endif

        </div>
    </div>
    @if($obj['type'] === 'series')
        <div class="flex flex-col py-4 px-2 ">
            <div class=" text-xl font-bold text-left rtl:text-right text-gray-500 dark:text-gray-400 pb-10">
                {{ __('statistic.backend.views pro clip') }}
            </div>
            @if(!empty($obj['clipsViews']))
                <div class="flex w-full ">
                    @include('backend.statistics.partial.clips-bar')
                </div>
            @else
                <div class="h4 dark:text-white">
                    {{ __('statistic.common.no statistic data available') }}
                </div>
            @endif
        </div>
    @else
        <div class="flex flex-col py-4 px-2 ">
            <div class=" text-xl font-bold text-left rtl:text-right text-gray-500 dark:text-gray-400 pb-10">
                {{ __('statistic.backend.clips statistics') }}
            </div>
            @if(!empty($obj['clipsViews']))
                <div class="flex w-full ">
                    @include('backend.statistics.partial.clip-views-line')
                </div>
            @else
                <div class="h4 dark:text-white">
                    {{ __('statistic.common.no statistic data available') }}
                </div>
            @endif
        </div>
    @endif
    <div class="flex flex-col py-2 px-2  justify-stretch pt-8">
        <div class=" text-xl font-bold text-left rtl:text-right text-gray-500 dark:text-gray-400 pb-10">
            {{ __('statistic.backend.geolocation statistics') }}
        </div>
        <div class="flex w-full pr-10">
            @if(!empty($obj['clipsViews']))
                <div class="w-2/3">
                    @include('backend.statistics.partial.geolocation-line')
                </div>
                <div class="flex w-1/3 pr-10">
                    @include('backend.statistics.partial.geolocation-pie')
                </div>
            @else
                <div class="flex w-full ">
                    <div class="h4 dark:text-white">
                        {{ __('statistic.common.no statistic data available') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
