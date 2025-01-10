@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black flex-col dark:text-white dark:border-white font-normal">
        <div class="font-semibold  text-2xl">
            {{ __('common.heading.create new podcast') }}
        </div>
    </div>
    <div class="flex">
        <form action="{{ route('podcasts.store') }}"
              method="POST"
              class="w-full">
            @csrf
            <div class="grid grid-cols-3">
                <div class="col-span-2">
                    <div class="flex flex-col gap-3 space-y-4 pt-4">
                        <x-form.input field-name="title"
                                      input-type="text"
                                      :placeholder="__('podcast.backend.podcast title form placeholder')"
                                      :value="old('title')"
                                      label="{{ __('common.forms.title') }}"
                                      :full-col="true"
                                      :required="true"
                        />

                        <x-form.textarea field-name="description"
                                         :value="old('description')"
                                         label="{{ __('common.forms.description') }}"
                        />

                        <x-form.select2-multiple field-name="hosts"
                                                 label="{{ trans_choice('common.metadata.host',2) }}"
                                                 select-class="select2-tides-presenters"
                                                 :model="null"
                                                 :items="[]"
                        />
                        <x-form.select2-multiple field-name="guests"
                                                 label="{{ trans_choice('common.guest', 2) }}"
                                                 select-class="select2-tides-presenters"
                                                 :model="null"
                                                 :items="[]"
                        />

                        <x-form.toggle-button :value="true"
                                              label="{{ __('common.forms.public') }}"
                                              field-name="is_published"
                        />
                        <x-form.input field-name="website_url"
                                      input-type="url"
                                      :value="old('website_url')"
                                      label="{{ __('common.forms.website url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                        <x-form.input field-name="apple_podcasts_url"
                                      input-type="url"
                                      :value="old('apple_podcasts_url')"
                                      label="{{ __('common.forms.apple podcasts url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                        <x-form.input field-name="spotify_url"
                                      input-type="url"
                                      :value="old('spotify_url')"
                                      label="{{ __('common.forms.spotify url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                    </div>
                </div>
                <div class="row-span-4">
                    <div class="flex flex-col items-center place-content-center text-lg pt-8 pb-4 border-b border-black
                    dark:text-white mb-4">
                        <div class="pb-4">
                            {{ __('podcast.common.podcast cover') }}
                        </div>
                        <div class="italic text-xs">
                            {{ __('podcast.backend.cover upload info text') }}
                        </div>

                    </div>

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
                </div>
                <div class="col-span-3 pt-10">
                    <div class="">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('common.forms.create podcast') }}
                        </x-button>
                        <a href="{{route('podcasts.index')}}">
                            <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                                {{ __('common.actions.cancel') }}
                            </x-button>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    </main>
@endsection
