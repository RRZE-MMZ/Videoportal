@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black flex-col dark:text-white dark:border-white font-normal">
        <div class="font-semibold  text-2xl">
            {{ $series->title }} [ ID : {{ $series->id }}]
        </div>
        <div class="py-4">
         <span class="text-md font-normal italic">
            -  {{ __('common.metadata.accessible via') }} : {{$series->fetchClipsAcls() }} /
             {{ __('series.common.created at') }} {{$series->created_at }}
         </span>
        </div>
    </div>

    <div class="flex justify-center content-center py-2 px-2">
        <form action="{{ route('series.update',$series) }}"
              method="POST"
              class=" @if(auth()->user()->isAdmin()) w-4/5 @else w-full @endif"
        >
            @csrf
            @method('PATCH')

            <div class="flex flex-col gap-3">

                <x-form.input field-name="title"
                              input-type="text"
                              :value="$series->title"
                              label="{{__('common.forms.title')}}"
                              :full-col="true"
                              :required="true"
                />

                <x-form.textarea field-name="description"
                                 :value="($series->description)"
                                 label="{{__('common.forms.description')}}"
                />

                <x-form.input field-name="opencast_series_id"
                              input-type="text"
                              :value="$series->opencast_series_id"
                              label="{{__('common.forms.Opencast series ID')}}"
                              :full-col="true"
                              :disabled="true"
                              :required="true"
                />

                <x-form.select2-single field-name="organization_id"
                                       label="{{__('common.forms.organization')}}"
                                       select-class="select2-tides-organization"
                                       model="organization"
                                       :selectedItem="$series->organization_id"
                />

                <x-form.select2-multiple field-name="presenters"
                                         :model="$series"
                                         label="{{trans_choice('common.menu.presenter',2)}}"
                                         select-class="select2-tides-presenters"
                                         :items="$series->presenters" />

                <x-form.password field-name="password"
                                 :value="$series->password"
                                 label="{{__('common.password')}}"
                                 :full-col="true"
                />

                <x-form.toggle-button :value="$series->is_public"
                                      label="{{__('common.forms.public available')}}"
                                      field-name="is_public"
                />

                <x-form.toggle-button :value="$series->allow_comments"
                                      label="{{__('common.metadata.allow comments')}}"
                                      field-name="allow_comments"
                />
            </div>
            @can('update-series', $series)
                <div class="pt-10">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        {{__('series.backend.Update Series')}}
                    </x-button>
                </div>
            @endcan
        </form>

        <div class="space-y-5 w-1/5 h-full pr-4">
            @include('backend.series.sidebar._series-owner')
            @can('delete-series',$series)
                @include('backend.series.sidebar._invite')
            @endif
            @include('backend.documents.upload',['resource' => $series ])
            @include('backend.images._card',['model'=> $series, 'type'=> 'series' ])
        </div>
    </div>


    <div
            class="">
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
                case '#clips':
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
        dark:text-white dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px">
                    <li class="me-2">
                        <a href="#clips"
                           x-on:click="activeTab = 1"
                           :class="activeTab === 1 ? activeClass : inactiveClass"
                           aria-current="page"
                        >
                            Clips
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="#actions"
                           x-on:click="activeTab = 3"
                           :class="activeTab === 3 ? activeClass : inactiveClass"
                        >
                            {{ __('series.common.actions') }}
                        </a>
                    </li>
                    @if ($opencastSeriesInfo->get('health')->get('status') === 'pass')
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
                        <a href="#comments-section"
                           x-on:click="activeTab = 4"
                           :class="activeTab === 4 ? activeClass : inactiveClass"
                        >
                            {{__('clip.frontend.comments')}}
                        </a>
                    </li>
                    <li>
                        <a href="#logs"
                           x-on:click="activeTab = 5"
                           :class="activeTab === 5 ? activeClass : inactiveClass"
                        >
                            {{ trans_choice('common.menu.activity',2) }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mt-6">
                <div x-show="activeTab === 1" id="clips" class="w-full ">
                    @include('backend.clips.list')
                </div>
                @if ($opencastSeriesInfo->get('health')->get('status') === 'pass')
                    <div x-show="activeTab === 2" id="opencast">
                        @include('backend.series.tabs.opencast.index')
                    </div>
                @endif
                <div x-show="activeTab === 3" id="actions">
                    @include('backend.series.buttons.actions')
                </div>
                <div x-show="activeTab === 4" id="comments-section">
                    <div class="flex flex-col pt-5 font-normal dark:text-white">
                        <div class="w-2/3">
                            <livewire:comments-section :model="$series" :type="'backend'" />
                        </div>
                    </div>
                </div>
                <div x-show="activeTab === 5" id="logs">
                    <div class="flex flex-col pt-10">
                        <livewire:activities-data-table :model="'series'" :object-i-d="$series->id" />
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
