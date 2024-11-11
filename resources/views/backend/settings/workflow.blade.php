@use(Carbon\Carbon)
@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black pb-2 text-2xl font-semibold dark:text-white dark:border-white">
        Video workflow settings
    </div>
    <div class="flex px-2 py-2">
        @php
            echo session()->get('errors');
        @endphp
        <form action="{{ route('settings.workflow.update') }}"
              method="POST"
              class="w-4/5 space-y-4">
            @csrf
            @method('PUT')
            <div class="bg-gray-200 border-2 rounded-2xl p-4 my-4 dark:bg-slate-800 dark:border-indigo-950 space-y-4">
                <div
                        class="flex border-b border-black pb-1 text-xl font-semibold text-indigo-800
                    dark:text-indigo-400 dark:border-white mb-4 ">
                    Opencast Settings
                </div>
                <x-form.input field-name="url"
                              input-type="url"
                              :value="$setting['url']"
                              label="Admin URL"
                              :fullCol="true"
                              :required="true" />
                <x-form.input field-name="username"
                              input-type="text"
                              :value="$setting['username']"
                              label="Admin username"
                              :fullCol="true"
                              :required="true" />
                <x-form.input field-name="password"
                              input-type="password"
                              :value="$setting['password']"
                              label="Admin password"
                              :fullCol="true"
                              :required="true" />
                <x-form.input field-name="default_workflow_id"
                              input-type="text"
                              :value="$setting['default_workflow_id']"
                              label="Default workflow ID"
                              :fullCol="true"
                              :required="true" />
                <x-form.input field-name="upload_workflow_id"
                              input-type="text"
                              :value="$setting['upload_workflow_id']"
                              label="Upload workflow ID"
                              :fullCol="true"
                              :required="true" />
                <div
                        class="mb-5 border-b border-black py-4 pb-2 text-base font-medium dark:text-white
                    dark:border-white"
                >
                    Opencast Archive Settings
                </div>
                <x-form.input field-name="archive_path"
                              input-type="text"
                              :value="$setting['archive_path']"
                              label="Archive path"
                              :fullCol="true"
                              :required="true" />
                <x-form.input field-name="assistants_group_name"
                              input-type="text"
                              :value="$setting['assistants_group_name']"
                              label="Opencast assistants group name"
                              :fullCol="true"
                              :required="true" />
                <div
                        class="mb-5 border-b border-black py-4 pb-2 text-base font-medium dark:text-white
                    dark:border-white"
                >
                    Opencast purge old events
                </div>
                <x-form.datepicker field-name="opencast_purge_end_date"
                                   label="Date for the last event to be deleted "
                                   :full-col="true"
                                   :value="old('opencast_purge_end_date', Carbon::now())" />
                <x-form.input field-name="opencast_purge_events_per_minute"
                              input-type="number"
                              :value="old('opencast_purge_events_per_minute', '20')"
                              label="Events per minute"
                              :full-col="true"
                              :required="false" />
                <div
                        class="mb-5 border-b border-black py-4 pb-2 text-base font-medium dark:text-white
                    dark:border-white"
                >
                    Opencast Themes
                </div>
                <div class="flex w-1/3">
                    <x-form.toggle-button :value="old('enable_themes_support', $setting['enable_themes_support'])"
                                          label="Enable themes support"
                                          field-name="enable_themes_support"
                    />
                </div>
                @if ($setting['enable_themes_support'])
                    <div class="grid grid-cols-1 gap-2">
                        @forelse ($setting['available_themes'] as $theme)
                            <div class="pt-4">
                                <x-form.input field-name="available_themes[{{ $loop->index }}][name]"
                                              input-type="text"
                                              :value="old('available_themes.'.$loop->index.'.name', $theme['name'])"
                                              label="Theme Name"
                                              :full-col="true"
                                              :disabled="true"
                                              :required="false" />

                                <x-form.input field-name="available_themes[{{ $loop->index }}][watermarkPosition]"
                                              input-type="text"
                                              :value="old('available_themes.'.$loop->index.'.watermarkPosition', $theme['watermarkPosition'])"
                                              label="Watermark Position"
                                              :full-col="true"
                                              :disabled="true"
                                              :required="false" />
                                <x-form.input field-name="available_themes[{{ $loop->index }}][id]"
                                              input-type="number"
                                              :value="old('available_themes.'.$loop->index.'.id', $theme['id'])"
                                              label="Theme ID"
                                              :full-col="true"
                                              :disabled="true"
                                              :required="false" />
                            </div>

                        @empty
                            <div class="text-md italic dark:text-white">
                                No themes found
                            </div>
                        @endforelse
                    </div>
                @endif

            </div>
            <div class="mt-10 space-x-4 pt-10 ">
                <x-button class="bg-blue-600 hover:bg-blue-700">
                    Update
                </x-button>
                <a href="{{ route('systems.status') }}">
                    <x-button type="button"
                              class="bg-gray-600 hover:bg-gray-700"
                    >
                        Cancel
                    </x-button>
                </a>
                @if($setting['enable_themes_support'])
                    <a href="{{ route('settings.workflow.fetchAndSaveOpencastThemes') }}" class="pl-10">
                        <x-button type="button"
                                  class="bg-green-600 hover:bg-green-700"
                        >
                            Fetch all available Opencast themes
                        </x-button>
                    </a>
                @endif
            </div>
        </form>
        g
    </div>
@endsection
