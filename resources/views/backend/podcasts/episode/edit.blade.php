@use('App\Enums\Content')
@use ('App\Models\Setting')
@extends('layouts.backend')

@section('content')
    @if($episode->hasVideoAsset() && ! $episode->getAssetsByType(Content::AUDIO)->first())
        <div>
            <div
                    x-data="{ show: false }"
                    x-init="() => {
            setTimeout(() => show = true, 0);
          }"
                    x-show="show"
                    x-description="Notification panel, show/hide based on alert state."
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-90"
                    class="mb-2 flex rounded-md bg-red-500 p-2 items-center py-2">
                <div class="flex-shrink-0">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-white" />
                </div>
                <div class="ml-3">
                    <p class="text-lg font-semibold leading-5 text-white">
                        {{ __('podcastEpisode.backend.migrate info text') }}
                    </p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="show = false"
                                class="inline-flex rounded-md p-1.5 text-white hover:bg-red-100
                                 focus:outline-none focus:bg-red-700 transition ease-in-out duration-150"
                                aria-label="Dismiss">
                            <x-heroicon-o-x-circle class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white py-2
     font-normal">
        <div class="flex w-full items-center align-middle my-2 ">
            <div class="text-3xl">
                {{$episode->episode_number}} - {{ $episode->title }} [ ID : {{ $episode->id }}]
            </div>
            <div class="flex space-x-4 pl-4 pt-1">
                @if(($previousNextEpisodesCollection->get('previous')))
                    @php
                        $previousEpisode = $previousNextEpisodesCollection->get('previous');
                    @endphp
                    <a href="{{ route('podcasts.episodes.edit',[$podcast,  $previousEpisode]) }}">
                        <x-button
                                :tooltip="true"
                                :tooltip-text="$previousEpisode->episode_number.' - '. $previousEpisode->title"
                                class="flex items-center bg-blue-600 hover:bg-blue-700 space-x-2  text-sm"
                        >
                            <div>
                                <x-heroicon-c-arrow-left class="w-4" />
                            </div>
                        </x-button>
                    </a>
                @endif
                @if(($previousNextEpisodesCollection->get('next')))
                    @php
                        $nextEpisode = $previousNextEpisodesCollection->get('next');
                    @endphp
                    <a href="{{ route('podcasts.episodes.edit',[$podcast,  $nextEpisode]) }}">
                        <x-button
                                :tooltip="true"
                                :tooltip-text="$nextEpisode->episode_number.' - '. $nextEpisode->title"
                                class="flex items-center bg-blue-600 hover:bg-blue-700 space-x-2  text-sm"
                        >
                            <div>
                                <x-heroicon-c-arrow-right class="w-4" />
                            </div>
                        </x-button>
                    </a>
                @endif
            </div>
        </div>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="flex">
        <form action="{{ route('podcasts.episodes.update', [$podcast,$episode]) }}"
              method="POST"
              class="w-full">
            @method('PUT')
            @csrf
            <div class="grid grid-cols-3">
                <div class="col-span-2">
                    <div class="flex flex-col gap-3 space-y-4 pt-4">
                        <x-form.input field-name="episode_number"
                                      input-type="number"
                                      :value="$episode->episode_number "
                                      label="{{ __('common.metadata.episode') }}"
                                      :full-col="false"
                                      :required="false"
                        />

                        <x-form.datepicker field-name="recording_date"
                                           label="{{ __('common.metadata.recording date') }}"
                                           :full-col="false"
                                           :value="$episode->recording_date" />

                        <x-form.input field-name="{{ __('common.forms.title') }}"
                                      input-type="text"
                                      :value="$episode->title"
                                      label="{{ __('common.forms.title') }}"
                                      :full-col="true"
                                      :required="true"
                        />

                        <x-form.textarea field-name="description"
                                         :value="$episode->description"
                                         label="{{ __('common.forms.description') }}"
                        />

                        <x-form.textarea field-name="notes"
                                         :value="$episode->notes"
                                         label="{{ __('common.metadata.notes') }}"
                        />

                        <x-form.textarea field-name="transcription"
                                         :value="$episode->transcription"
                                         label="{{ __('common.metadata.transcript') }}"
                        />

                        <x-form.select2-multiple field-name="hosts"
                                                 label="{{ trans_choice('common.metadata.host', 2) }}"
                                                 select-class="select2-tides-presenters"
                                                 :model="$episode"
                                                 :items="$episode->getPrimaryPresenters()"
                        />

                        <x-form.select2-multiple field-name="guests"
                                                 label="{{ trans_choice('common.guest', 2) }}"
                                                 select-class="select2-tides-presenters"
                                                 :model="$episode"
                                                 :items="$episode->getPrimaryPresenters(primary: false)"
                        />

                        <x-form.toggle-button :value="$episode->is_published"
                                              label="{{ __('common.forms.public') }}"
                                              field-name="is_published"
                        />
                        <x-form.input field-name="website_url"
                                      input-type="url"
                                      :value="$episode->website_url"
                                      label="{{ __('common.forms.website url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                        <x-form.input field-name="apple_podcasts_url"
                                      input-type="url"
                                      :value="$episode->apple_podcasts_url"
                                      label="{{ __('common.forms.apple podcasts url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                        <x-form.input field-name="spotify_url"
                                      input-type="url"
                                      :value="$episode->spotify_url"
                                      label="{{ __('common.forms.spotify url') }}"
                                      :full-col="true"
                                      :required="false"
                        />
                    </div>
                </div>
                <div class="row-span-4">
                    @if($episode->getAssetsByType(Content::AUDIO)->first())
                        <div>
                            <div>
                                <div class="mt-4 dark:text-white ">
                                    <audio id="player" class="w-full" controls>
                                        <source
                                                src="{{ getProtectedUrl($episode->getAssetsByType(Content::AUDIO)->first()->path) }}"
                                                type="audio/mp3" />
                                        <source src="/path/to/audio.ogg" type="audio/ogg" />
                                    </audio>
                                </div>
                                <!-- Add more episodes as needed -->
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col items-left my-4 py-4 space-y-4">
                        <div class="text-lg dark:text-white border-b border-black dark:border-white pl-4 py-2">
                            {{ __('') }}
                        </div>
                        <div class="flex space-x-2">
                            <div>
                                <a href="{{ route('podcasts.edit', $podcast) }}">
                                    <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                                        {{ __('podcast.common.edit podcast') }}
                                    </x-button>
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('frontend.podcasts.episode.show', [$podcast, $episode])  }}">
                                    <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                                        {{ __('series.backend.actions.go to public page') }}
                                    </x-button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex-row  pr-4">
                        <div class="text-lg  dark:text-white pt-4 ">
                            <span class="font-bold">
                                {{ __('podcastEpisode.backend.podcast owner') }}
                            </span>
                            <span class="italic">
                                       @if(is_null($episode->owner))
                                    {{ __('podcastEpisode.backend.no podcast owner') }}
                                @else
                                    {{$episode->owner?->getFullNameAttribute().'-'.$episode->owner?->username}}
                                @endif

                                </span>
                        </div>
                        @can('change-series-owner')
                            <div class="w-full pt-6 dark:text-white">
                                <div class="w-full pb-6">
                                    <label>
                                        <select
                                                class="p-2 w-full select2-tides-users
                                        focus:outline-none focus:bg-white focus:border-blue-500"
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
                        <img
                                @if(!is_null($episode->cover))
                                    src="{{ asset('images/'.$episode->cover->file_name) }}"
                                alt="{{ $episode->cover->description }}"
                                @else
                                    src="{{ asset('images/'.$podcast->cover->file_name) }}"
                                alt="{{ $podcast->cover->description }}"
                                @endif

                                class="w-full h-auto rounded-md">
                    </div>
                    @if(is_null($episode->cover))
                        <div class="text-lg dark:text-white italic">
                            {{ __('podcastEpisode.backend.inherited from podcast header') }}
                        </div>
                    @endif
                    <div class="flex flex-col items-center place-content-center text-lg pt-8 pb-4 border-b border-black
                    dark:text-white mb-4">
                        <div class="pb-4">
                            {{ __('podcastEpisode.backend.upload a new podcast cover') }}
                        </div>
                        <div class="italic text-xs">
                            {{ __('podcastEpisode.backend.please prefer a resolution of 1400x1400px') }}
                        </div>

                    </div>

                    <input type="file"
                           name="image"
                           class="filepond-input1"
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
                            {{ __('podcastEpisode.backend.actions.podcast episode update') }}
                        </x-button>
                        <a href="{{route('podcasts.edit', $podcast)}}">
                            <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                                {{__('common.actions.cancel')}}
                            </x-button>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div
            class="pt-10">
        <div x-data="{
            activeTab:1,
            activeClass: 'inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500',
            inactiveClass : 'inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300',
            init(){
             this.updateActiveTabFromURL();
            window.addEventListener('hashchange', () => this.updateActiveTabFromURL());
            },
            updateActiveTabFromURL() {
            const hash = window.location.hash;
            switch(hash) {
                case '#assets':
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
        }
    }" class="w-full">
            <div class="text-md font-medium text-center text-gray-500 border-b border-gray-200
        dark:text-white dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px">
                    <li class="me-2">
                        <a href="#assets"
                           x-on:click="activeTab = 1"
                           :class="activeTab === 1 ? activeClass : inactiveClass"
                           aria-current="page"
                        >
                            {{ trans_choice('common.menu.asset',2) }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="#actions"
                           x-on:click="activeTab = 2"
                           :class="activeTab === 2 ? activeClass : inactiveClass"
                        >
                            {{ __('series.common.actions') }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="#comments-section"
                           x-on:click="activeTab = 3"
                           :class="activeTab === 3 ? activeClass : inactiveClass"
                        >
                            <div class="flex items-center">
                                <div>
                                    {{__('clip.frontend.comments')}}
                                </div>
                                @if($count  = $episode->comments()->backend()->count() > 0)
                                    <span
                                            class="inline-flex items-center justify-center w-4 h-4 ms-2 text-xs
                                    font-semibold text-white bg-blue-500 rounded-full">
                                        {{ $count }}
                                    </span>
                                @endif
                            </div>

                        </a>
                    </li>
                    <li>
                        <a href="#logs"
                           x-on:click="activeTab = 4"
                           :class="activeTab === 4 ? activeClass : inactiveClass"
                        >
                            {{ trans_choice('common.menu.activity', 2) }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mt-6">
                <div x-show="activeTab === 1" id="clips" class="w-full ">
                    @include('backend.assets.list', [
                    'obj' => $episode,
                    'assets'=> $episode->assets
                    ])
                    <form action="{{ route(
                                            'admin.podcasts.episode.transferPodcastAudioFile',
                                            compact('podcast', 'episode')
                                            ) }}"
                          method="POST"
                          class="w-full"
                    >
                        @csrf

                        <input type="file"
                               name="asset"
                               class="filepond-input2"
                               data-max-file-size=" 100MB"
                        />
                        @error('image')
                        <div class="col-start-2 col-end-6">
                            <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
                        </div>
                        @enderror
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('podcastEpisode.backend.actions.upload audio file') }}
                        </x-button>

                    </form>
                </div>
                <div x-show="activeTab === 2" id="actions">
                    <div class="flex items-center pt-3 space-x-6 pb-10">
                        <a href="{{route('frontend.podcasts.episode.show', compact('podcast','episode'))}}">
                            <x-button type='button' class="bg-blue-600 hover:bg-blue-700">
                                {{ __('series.backend.actions.go to public page') }}
                            </x-button>
                        </a>
                        <a href="#">
                            <x-button class="bg-blue-600 hover:bg-blue-700">
                                {{ trans_choice('common.menu.statistic', 2) }}
                            </x-button>
                        </a>
                        <a href="#">
                            <x-button class="bg-blue-600 hover:bg-blue-700">
                                {{ __('podcastEpisode.backend.actions.generate transcript') }}
                            </x-button>
                        </a>
                        <x-modals.delete :route="route('podcasts.episodes.destroy', compact('podcast','episode'))">
                            <x-slot:title>
                                {{__('podcastEpisode.backend.delete.modal title',['episode_title'=>$episode->title])}}
                            </x-slot:title>
                            <x-slot:body>
                                {{__('podcastEpisode.backend.delete.modal body')}}
                            </x-slot:body>
                        </x-modals.delete>
                    </div>

                </div>
                <div x-show="activeTab === 3" id="comments-section">
                    <div class="flex flex-col pt-5 font-normal dark:text-white">
                        <div class="w-2/3">
                            <livewire:comments-section :model="$episode" :type="'backend'" />
                        </div>
                    </div>
                </div>
                <div x-show="activeTab === 4" id="logs">
                    <div class="flex flex-col pt-10">
                        <livewire:activities-data-table :model="'podcastEpisode'" :object-i-d="$episode->id" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection
