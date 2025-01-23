@use(App\Enums\Acl)
@use(Carbon\Carbon)
@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal">
        <div class="flex w-full items-center">
            <div class="">
                <span class="text-3xl"> [ ID: {{ $clip->id }} ] {{ $clip->title }}</span>
            </div>
            <div class="flex space-x-2 pl-4">
                @if(!is_null($previousNextClipCollection->get('previousClip')))
                    @php
                        $previousClip = $previousNextClipCollection->get('previousClip');
                    @endphp
                    <a href="{{ route('clips.edit', $previousClip) }}">
                        <x-button
                                :tooltip="true"
                                :tooltip-text="$previousClip->episode.' - '. $previousClip->title"
                                class="flex items-center bg-blue-600 hover:bg-blue-700 space-x-2  text-sm"
                        >
                            <div>
                                <x-heroicon-c-arrow-left class="w-4" />
                            </div>
                        </x-button>
                    </a>
                @endif

                @if(!is_null($previousNextClipCollection->get('nextClip')))
                    @php
                        $nextClip = $previousNextClipCollection->get('nextClip');
                    @endphp
                    <a href="{{ route('clips.edit',$nextClip) }}">
                        <x-button
                                :tooltip="true"
                                :tooltip-text="$nextClip->episode.' - '. $nextClip->title"
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
        <div class="flex pt-2 text-sm italic pb-2">
            <span
                    class="pl-2"> {{ __('common.created') }}
                @if(!is_null($clip->owner_id))
                    {{ __('common.by') }} {{ $clip->owner->getFullNameAttribute() }} ({{ $clip->owner->username }})
                @endif
                {{ __('common.at') }} {{$clip->created_at}}</span>
        </div>
    </div>
    <div class="flex px-2 py-2">
        <div class="w-4/5">

            @if($clip->assets()->formatVideo()->count() > 0 || $clip->is_livestream)
                @include('backend.clips.main._player')
            @endif
            <form action="{{ route('clips.update', $clip) }}"
                  method="POST"
            >
                @csrf
                @method('PATCH')
                <div class="flex flex-col gap-3">

                    <x-form.input field-name="episode"
                                  input-type="number"
                                  :value="old('episode', $clip->episode)"
                                  label="{{ __('common.metadata.episode') }}"
                                  :full-col="false"
                                  :required="true" />

                    <x-form.datepicker field-name="recording_date"
                                       label="{{ __('common.metadata.recording date') }}"
                                       :full-col="false"
                                       :value="old('recording_date', $clip->recording_date)" />

                    <x-form.input field-name="title"
                                  input-type="text"
                                  :value="old('title', $clip->title)"
                                  label="{{ __('common.metadata.title') }}"
                                  :fullCol="true"
                                  :required="true" />

                    @if($clip->series->chapters()->count() > 0)
                        <x-form.select2-single field-name="chapter_id"
                                               label="{{ __('common.metadata.chapter') }}"
                                               select-class="select2-tides"
                                               model="chapter"
                                               :where-i-d="old('series_id', $clip->series->id)"
                                               :selectedItem="old('chapter_id', $clip->chapter_id)"
                        />
                    @endif
                    <x-form.textarea field-name="description"
                                     :value="old('description', $clip->description)"
                                     label="{{ __('common.metadata.description') }}" />

                    <x-form.select2-single field-name="organization_id"
                                           label="{{ __('common.metadata.organization') }}"
                                           select-class="select2-tides-organization"
                                           model="organization"
                                           :selectedItem="old('organization_id', $clip->organization_id)"
                    />
                    <x-form.select2-single field-name="language_id"
                                           label="{{ __('common.metadata.language') }}"
                                           select-class="select2-tides"
                                           model="language"
                                           :selectedItem="old('language_id', $clip->language_id)"
                    />

                    <div class="mb-2 border-b border-solid border-b-black pb-2 text-left text-xl font-bold
                            dark:text-white dark:border-white"
                    >
                        {{ __('common.metadata.metadata') }}
                    </div>
                    @can('administrate-superadmin-portal-pages')
                        <x-form.input field-name="opencast_event_id"
                                      input-type="text"
                                      :value="old('opencast_event_id', $clip->opencast_event_id)"
                                      label="{{ __('common.metadata.opencast_event_id') }}"
                                      :fullCol="false"
                                      :required="false" />
                    @endcannot
                    <x-form.select2-single field-name="context_id"
                                           label="{{ __('common.metadata.context') }}"
                                           select-class="select2-tides"
                                           model="context"
                                           @cannot('administrate-superadmin-portal-pages') disabled @endcannot
                                           :selectedItem="old('context_id', $clip->context_id)"
                    />
                    <x-form.select2-single field-name="context_id"
                                           label="{{ __('common.metadata.context') }}"
                                           select-class="select2-tides"
                                           model="context"
                                           :selectedItem="old('context_id', $clip->context_id)"
                    />
                    <x-form.select2-single field-name="format_id"
                                           label="{{ __('common.metadata.format') }}"
                                           select-class="select2-tides"
                                           model="format"
                                           :selectedItem="old('format_id', $clip->format_id)"
                    />
                    <x-form.select2-single field-name="type_id"
                                           label="{{ __('common.metadata.type') }}"
                                           select-class="select2-tides"
                                           model="type"
                                           :selectedItem="old('clip_id', $clip->type_id)"
                    />

                    <x-form.select2-single field-name="semester_id"
                                           label="{{ __('common.metadata.semester') }}"
                                           select-class="select2-tides"
                                           model="semester"
                                           :selectedItem="old('semester_id', $clip->semester_id)"
                    />

                    <x-form.select2-multiple field-name="presenters"
                                             :model="$clip"
                                             label="{{trans_choice('common.menu.presenter',2)}}"
                                             select-class="select2-tides-presenters"
                                             :items="$clip->presenters" />

                    <x-form.select2-multiple field-name="tags"
                                             :model="$clip"
                                             label="{{ __('common.metadata.tags') }}"
                                             select-class="select2-tides-tags"
                                             :items="$clip->tags" />

                    <x-form.select2-multiple field-name="acls"
                                             :model="$clip"
                                             label="{{ __('common.metadata.accessible via') }}"
                                             select-class="select2-tides" />
                    @can('administrate-admin-portal-pages')
                        <x-form.password field-name="password"
                                         :value="old('password', $clip->password)"
                                         label="{{ __('common.metadata.password') }}"
                                         :full-col="false"
                        />
                    @endcan
                    <x-form.toggle-button :value="old('allow_comments', $clip->allow_comments)"
                                          label="{{ __('common.metadata.allow comments') }}"
                                          field-name="allow_comments"
                    />

                    <x-form.toggle-button :value="old('is_public', $clip->is_public)"
                                          label="{{ __('common.forms.public available') }}"
                                          field-name="is_public"
                    />

                    <x-form.toggle-button :value="old('is_livestream', $clip->is_livestream)"
                                          label="{{ __('common.metadata.livestream clip') }}"
                                          field-name="is_livestream"
                    />

                    <x-date-time-picker
                            :has-time-availability="old('has_time_availability', $clip->has_time_availability)"
                            :time-availability-start="old('time_availability_start', $clip->time_availability_start)"
                            :time-availability-end="old('time_availability_end', $clip->time_availability_end)"
                            name="time_availability"
                            label="Time availability">
                    </x-date-time-picker>

                </div>

                <div class="pt-10">
                    <x-button type="submit" class="bg-blue-600 hover:bg-blue-700">
                        {{ __('common.actions.update') }}
                    </x-button>
                </div>
            </form>
        </div>

        <div class="h-full w-1/5 pr-4 space-y-5">

            @if(! is_null($clip->series_id) )
                @include('backend.clips.sidebar._series-options')
            @else
                @include('backend.clips.sidebar._assign-series')
            @endif

            @if ($opencastConnectionCollection['status']==='pass' && !$clip->is_livestream)
                @include('backend.clips.sidebar._ingest-video')
            @else
                @if(!$clip->is_livestream)
                    @include('backend.clips.sidebar._upload-video')
                @endif
            @endif

            @if(auth()->user()->isAdmin() && $clip->acls->pluck('id')->contains(Acl::LMS()))
                @include('backend.clips.sidebar._lms_test_link')
            @endif

            @include('backend.documents.upload',['resource'=> $clip ])
            @include('backend.images._card',['model'=> $clip, 'type'=> 'clip' ])
        </div>
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
                case '#opencast':
                    this.activeTab = 2;
                    break;
                case '#actions':
                    this.activeTab = 3;
                    break;
                case '#comments-section':
                    this.activeTab = 4;
                    break;
                case '#logs':
                    this.activeTab = 5;
                    break;
                default:
                    this.activeTab = 1; // Default to the first tab if no matching hash
            }
        }
    }" class="w-full">
            <div class="text-md font-medium text-center text-gray-500 border-b border-gray-200
        dark:text-gray-400 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px">
                    <li class="me-2">
                        <a href="#assets"
                           x-on:click="activeTab = 1"
                           :class="activeTab === 1 ? activeClass : inactiveClass"
                           aria-current="page"
                        >
                            {{trans_choice('common.menu.asset',2)}}
                        </a>
                    </li>
                    @if(isset($opencastSeriesInfo['health']) && $opencastSeriesInfo['health'])
                        <li class="me-2">
                            <a href="#opencast"
                               x-on:click="activeTab = 2"
                               :class="activeTab === 2 ? activeClass : inactiveClass"
                            >
                                Video Workflow
                            </a>
                        </li>
                    @endif
                    <li class="me-2">
                        <a href="#actions"
                           x-on:click="activeTab = 3"
                           :class="activeTab === 3 ? activeClass : inactiveClass"
                        >
                            {{ __('series.common.actions') }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="#comments-section"
                           x-on:click="activeTab = 4"
                           :class="activeTab === 4 ? activeClass : inactiveClass"
                        >
                            <div class="flex items-center">
                                <div>
                                    {{__('clip.frontend.comments')}}
                                </div>
                                @if($count  = $clip->comments()->backend()->count() > 0)
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
                           x-on:click="activeTab = 5"
                           :class="activeTab === 5 ? activeClass : inactiveClass"
                        >
                            {{trans_choice('common.menu.activity', 2)}}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mt-6">
                <div x-show="activeTab === 1" id="clips" class="w-full ">
                    @include('backend.assets.list', ['assets'=>$clip->assets,'obj'=> $clip])
                </div>
                <div x-show="activeTab === 2" id="opencast">
                </div>
                <div x-show="activeTab === 3" id="actions">
                    <div class="flex items-center pt-3 space-x-6 pb-10">
                        <a href="{{route('frontend.clips.show', $clip)}}">
                            <x-button type='button' class="bg-blue-600 hover:bg-blue-700">
                                {{ __('clip.backend.actions.go to clip public page') }}
                            </x-button>
                        </a>
                        <a href="{{ route('statistics.clip', $clip) }}">
                            <x-button class="bg-blue-600 hover:bg-blue-700">
                                {{ trans_choice('common.menu.statistic', 2) }}
                            </x-button>
                        </a>
                        @can('administrate-superadmin-portal-pages')
                            @if ($clip->assets()->count())
                                <a href="{{route('admin.clips.triggerSmilFiles', $clip)}}">
                                    <x-button type='button' class="bg-blue-600 hover:bg-blue-700">
                                        {{ __('clip.backend.actions.re-trigger streaming smil files') }}
                                    </x-button>
                                </a>
                            @endif
                        @endcan

                        @can('administrate-admin-portal-pages')
                            @if(!$clip->is_livestream )
                                <a href="{{route('admin.clips.dropzone.listFiles', $clip)}}">
                                    <x-button type='button' class="bg-blue-600 hover:bg-blue-700">
                                        {{ __('clip.backend.actions.transfer files from drop zone') }}
                                    </x-button>
                                </a>
                            @endif
                        @endcan
                        @if($opencastConnectionCollection['status']==='pass' && !$clip->is_livestream)
                            <a href="{{route('admin.clips.opencast.listEvents', $clip)}}">
                                <x-button type='button' class="bg-blue-600 hover:bg-blue-700">
                                    {{ __('clip.backend.actions.upload already transcoded recording') }}
                                </x-button>
                            </a>
                        @endif
                        <x-modals.delete :route="route('clips.destroy', $clip)">
                            <x-slot:title>
                                {{__('clip.backend.delete.modal title',['clip_title'=>$clip->title])}}
                            </x-slot:title>
                            <x-slot:body>
                                {{__('clip.backend.delete.modal body')}}
                            </x-slot:body>
                        </x-modals.delete>
                    </div>

                </div>
                <div x-show="activeTab === 4" id="comments-section">
                    <div class="flex flex-col pt-5 font-normal dark:text-white">
                        <div class="w-2/3">
                            <livewire:comments-section :model="$clip" :type="'backend'" />
                        </div>
                    </div>
                </div>
                <div x-show="activeTab === 5" id="logs">
                    <div class="flex flex-col pt-10">
                        <livewire:activities-data-table :model="'clip'" :object-i-d="$clip->id" />
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
