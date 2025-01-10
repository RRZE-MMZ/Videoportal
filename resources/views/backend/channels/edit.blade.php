@use(Illuminate\Support\Str)
@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal">
        <div class="flex w-full items-center justify-between">
            <div class="">
                <span class="text-3xl"> {{ trans_choice('common.menu.channel', 1) }} : {{ $channel->name }}</span>
            </div>
        </div>
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
                            alt="{{ __('channel.common.channel banner') }}"
                            class="object-cover w-full h-full" />
                </div>

            </div>
        </div>
        <form action="{{ route('channels.update', $channel) }}" method="POST" class="w-full">
            @csrf
            @method('PATCH')
            <div class="flex flex-col gap-3">
                <x-form.input field-name="name"
                              input-type="text"
                              :value="old('name', $channel->name)"
                              label="{{ __('common.name') }}"
                              :full-col="false"
                              :required="true" />
                <x-form.textarea field-name="description"
                                 :value="old('description', $channel->description)"
                                 label="{{ __('common.description') }}" />
                <div class="pt-10">
                    <x-button type="submit" class="bg-blue-600 hover:bg-blue-700">
                        {{ __('common.actions.update') }}
                    </x-button>
                </div>
            </div>
        </form>
    </div>
    <form action="{{ route('channels.uploadBannerImage', $channel) }}"
          method="POST"
          class="w-full"
    >
        @csrf

        <input type="file"
               name="image"
               class="filepond"
               data-max-file-size="10MB"
        />

        @error('image')
        <div class="col-start-2 col-end-6">
            <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
        </div>
        @enderror
        <x-button class="bg-blue-600 hover:bg-blue-700">
            <div class="flex">
                <x-heroicon-o-arrow-up-circle class="h-6 w-6" />
                <span class="pl-4">
                            {{ __('channel.backend.actions.upload channel banner image') }}
                    </span>
            </div>
        </x-button>

    </form>
@endsection
