@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black flex-col dark:text-white dark:border-white font-normal">
        <div class="font-semibold  text-2xl">
            {{ $podcast->title }} [ PodcastID : {{ $podcast->id }}]
        </div>
    </div>
    <div class="flex">
        <form action="{{ route('podcasts.update', $podcast) }}"
              method="POST"
              class="w-full">
            @method('PATCH')
            @csrf
            <div class="grid grid-cols-3">
                <div class="col-span-2">
                    <div class="flex flex-col gap-3 space-y-4 pt-4">
                        <x-form.input field-name="title"
                                      input-type="text"
                                      :value="$podcast->title"
                                      label="{{ __('common.forms.title') }}"
                                      :full-col="true"
                                      :required="true"
                        />

                        <x-form.textarea field-name="description"
                                         :value="$podcast->description"
                                         label="{{ __('common.forms.description') }}"
                        />

                        <x-form.select2-multiple field-name="hosts"
                                                 label="{{ trans_choice('common.metadata.host',2) }}"
                                                 select-class="select2-tides-presenters"
                                                 :model="$podcast"
                                                 :items="$podcast->getPrimaryPresenters()"
                        />

                        <x-form.select2-multiple field-name="guests"
                                                 label="{{ trans_choice('common.metadata.guest',2) }}"
                                                 select-class="select2-tides-presenters"
                                                 :model="$podcast"
                                                 :items="$podcast->getPrimaryPresenters(primary: false)"
                        />

                        <x-form.toggle-button :value="$podcast->is_published"
                                              label="{{ __('common.forms.public') }}"
                                              field-name="is_published"
                        />
                        <x-form.input field-name="website_url"
                                      input-type="url"
                                      :value="$podcast->website_url"
                                      label="{{ __('common.forms.website url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                        <x-form.input field-name="apple_podcasts_url"
                                      input-type="url"
                                      :value="$podcast->apple_podcasts_url"
                                      label="{{ __('common.forms.apple podcasts url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                        <x-form.input field-name="spotify_url"
                                      input-type="url"
                                      :value="$podcast->spotify_url"
                                      label="{{ __('common.forms.spotify url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                    </div>
                </div>
                <div class="row-span-4 pr-4">
                    <div class="flex-row">
                        <div class="text-lg  dark:text-white pt-4 ">
                            <span class="font-bold">{{ __('podcast.backend.podcast owner') }}:</span>
                            <span class="italic">
                                       @if(is_null($podcast->owner))
                                    {{ __('podcast.backend.podcast no owner info text') }}
                                @else
                                    {{$podcast->owner?->getFullNameAttribute().'-'.$podcast->owner?->username}}
                                @endif

                                </span>
                        </div>
                        @can('change-series-owner')
                            <div class="w-full pt-6 dark:text-white">
                                <div class="w-full pb-6">
                                    <label>
                                        <select
                                                class="p-2 w-full select2-tides-users
                                        focus:outline-hidden focus:bg-white focus:border-blue-500 "
                                                name="owner_id"
                                                style="width: 100%"
                                        >
                                        </select>
                                    </label>
                                </div>
                            </div>
                        @endcan
                    </div>
                    <div class="flex w-full">
                        <img src="{{ asset('images/'.$podcast->cover->file_name) }}"
                             alt="{{ $podcast->cover->description }}"
                             class="w-full h-auto rounded-md">
                    </div>
                    <div class="flex flex-col items-center place-content-center text-lg pt-8 pb-4 border-b border-black
                    dark:text-white mb-4">
                        <div class="pb-4">
                            {{ __('podcast.backend.actions.upload a new podcast cover') }}
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
                        <x-button :type="'submit'" class="bg-blue-600 hover:bg-blue-700">
                            Podcast {{ __('common.actions.update') }}
                        </x-button>
                        <a href="{{route('podcasts.index')}}">
                            <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                                {{__('common.actions.cancel')}}
                            </x-button>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="pt-8">
        <div x-data="{
        activeTab: 1,
        activeClass: 'inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500',
        inactiveClass: 'inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300',
        init() {
            this.updateActiveTabFromURL();
            window.addEventListener('hashchange', () => this.updateActiveTabFromURL());
        },
        updateActiveTabFromURL() {
            const hash = window.location.hash;
            switch (hash) {
                case '#episodes':
                    this.activeTab = 1;
                    break;
                case '#actions':
                    this.activeTab = 2;
                    break;
                case '#comments-section':
                    this.activeTab = 3;
                    break;
                case '#logs':
                    this.activeTab = 4;
                    break;
                default:
                    this.activeTab = 1; // Default to the first tab if no matching hash
            }
            this.scrollToActiveTab();
        },
        scrollToActiveTab() {
            this.$refs['container' + (this.activeTab - 1)].scrollIntoView({ behavior: 'smooth' });
        }
    }" class="w-full">
            <div
                    class="text-lg font-medium text-center text-gray-500 border-b border-gray-200 dark:text-white dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px">
                    <li class="me-2">
                        <a href="#episodes"
                           x-on:click="activeTab = 1; scrollToActiveTab()"
                           :class="activeTab === 1 ? activeClass : inactiveClass"
                           aria-current="page"
                           x-ref="tab1"
                        >
                            {{ trans_choice('common.menu.episode', 2) }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="#actions"
                           x-on:click="activeTab = 2; scrollToActiveTab()"
                           :class="activeTab === 2 ? activeClass : inactiveClass"
                           x-ref="tab2"
                        >
                            {{ __('series.common.actions') }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="#comments-section"
                           x-on:click="activeTab = 3; scrollToActiveTab()"
                           :class="activeTab === 3 ? activeClass : inactiveClass"
                           x-ref="tab3"
                        >
                            {{__('clip.frontend.comments')}}
                        </a>
                    </li>
                    <li>
                        <a href="#logs"
                           x-on:click="activeTab = 4; scrollToActiveTab()"
                           :class="activeTab === 4 ? activeClass : inactiveClass"
                           x-ref="tab4"
                        >
                            {{ trans_choice('common.menu.activity', 2) }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mt-6">
                <div x-show="activeTab === 1" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0"
                     id="clips" class="w-full overflow-hidden" x-ref="container0">
                    @include('backend.podcasts.episode.list')
                </div>
                <div x-show="activeTab === 2" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0"
                     id="actions" class="overflow-hidden" x-ref="container1">
                    @include('backend.podcasts.buttons.actions', $podcast)
                </div>
                <div x-show="activeTab === 3" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0"
                     id="comments-section" class="overflow-hidden" x-ref="container2">
                    <div class="flex flex-col pt-5 font-normal dark:text-white">
                        <div class="w-2/3">
                            <livewire:comments-section :model="$podcast" :type="'backend'" />
                        </div>
                    </div>
                </div>
                <div x-show="activeTab === 4" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0"
                     id="logs" class="overflow-hidden" x-ref="container3">
                    <div class="flex flex-col pt-10">
                        <livewire:activities-data-table :model="'podcast'" :object-i-d="$podcast->id" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    </main>
@endsection
