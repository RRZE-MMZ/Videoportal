@extends('layouts.frontend')

@section('content')
    <div class="container mx-auto mt-16 md:mt-16 dark:text-white">

        <div class="flex justify-between border-b-2 border-black pb-2 dark:border-white">
            <h2 class="text-2xl font-bold ">
                {{ $channel->name }}
            </h2>
            @can('edit-channel', $channel)
                <x-form.button :link="route('channels.edit',$channel)" type="submit"
                               text="{{ __('channel.backend.edit channel')  }}" />
            @endcan
        </div>
        <div class="flex flex-col px-2 py-2 pt-10">
            <div class="w-full items-center align-middle content-center pb-10">
                <div class="">
                    <div class="w-120 h-72 rounded-full mx-auto
                ">
                        <img
                                src="{{ (is_null($channel->banner_url))
                    ? "/images/channels_banners/generic_channel_banner.png"
                    : '/'.$channel->banner_url }}"
                                alt="channel banner"
                                class="object-cover w-full h-full" />
                    </div>
                </div>
            </div>
        </div>
        <div>
            <figure class="md:flex bg-slate-100 rounded-xl p-8  md:p-0 dark:bg-slate-800">
                <img class="w-24 h-24 rounded-xs mx-auto mt-10"
                     src="@if(!is_null($channel->owner->presenter))
                                             {{ $channel->owner->presenter->getImageUrl() }}
                                             @else/images/DummyMann.png>
                                        @endif"
                     alt=""
                     width="384"
                     height="512"
                >
                <div class="pt-6 md:p-8 text-left space-y-4">
                    <blockquote>
                        <p class="text-lg font-medium">
                            {!! $channel->description !!}
                        </p>
                    </blockquote>
                    <figcaption class="font-medium">
                        <div class="text-sky-500 dark:text-sky-400">
                            {{ $channel->owner->getFullNameAttribute() }}
                        </div>
                    </figcaption>
                </div>
            </figure>
        </div>
        <div class="flex w-full items-end border-b justify-content-between pb-4 border-black dark:border-white "
        >
            <div class="flex w-full items-end justify-between pt-4 pb-2">
                <div class="text-2xl dark:text-white">  {{  __('homepage.series.Recently added!') }} </div>
                <a href="{{ route('frontend.series.index') }}"
                   class="text-sm underline dark:text-white ">{{__('homepage.series.more series') }}</a>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-4 pt-8 border-b-2 border-black dark:border-white ">
            @forelse($channel->owner->getAllSeries()->withLastPublicClip()->get() as $single)

                @include('backend.series._card',[
                        'series'=> $single,
                        'route' => 'admin'
                        ])
            @empty
                <div class="dark:text-white text-2xl pt-10">
                    {{ __('homepage.series.no series found' )}}
                </div>
            @endforelse
        </div>
    </div>

@endsection
