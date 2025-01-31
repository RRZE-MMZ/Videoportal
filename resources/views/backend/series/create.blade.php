@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            {{ __('common.heading.create new series') }}
        </div>
    </div>
    <div class="flex py-2 px-2">
        <form action="{{ route('series.store') }}"
              method="POST"
              class="w-4/5">
            @csrf

            <div class="flex flex-col gap-3">
                <x-form.input field-name="title"
                              input-type="text"
                              :value="old('title')"
                              label="{{ __('common.forms.title') }}"
                              :full-col="true"
                              :required="true"
                />

                <x-form.textarea field-name="description"
                                 :value="old('description')"
                                 label="{{ __('common.forms.description') }}"
                />

                <x-form.select2-single field-name="organization_id"
                                       label="{{ __('common.forms.organization') }}"
                                       select-class="select2-tides-organization"
                                       model="organization"
                                       :selectedItem="(old('organization_id'))?? 1 "
                />

                <x-form.select2-multiple field-name="presenters"
                                         label="{{ trans_choice('common.menu.presenter', 2) }}"
                                         select-class="select2-tides-presenters"
                                         :model="null"
                                         :items="[]"
                />

                <x-form.select2-multiple field-name="acls"
                                         label="{{ __('series.common.access via') }}"
                                         :model="null"
                                         select-class="select2-tides"
                />

                <x-form.password field-name="password"
                                 :value="old('password')"
                                 label="{{ __('common.password') }}"
                                 :full-col="true"
                />
                <x-form.select2-multiple field-name="tags"
                                         label="{{ __('common.metadata.tags') }}"
                                         select-class="select2-tides-tags"
                                         :model="null"
                                         :items="[]"
                />
                <x-form.toggle-button :value="true"
                                      label="{{ __('common.forms.public available') }}"
                                      field-name="is_public"
                />
                <x-form.toggle-button :value="false"
                                      label="{{ __('common.metadata.allow comments') }}"
                                      field-name="allow_comments"
                />

                <div class="flex content-center items-center mb-6">
                </div>
                <div class="col-span-7 w-4/5">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        {{ __('common.forms.create series') }}
                    </x-button>
                    <a href="{{route('dashboard')}}">
                        <x-button type="button" class="ml-3 bg-green-600 hover:bg-green-700">
                            {{ __('common.actions.cancel') }}
                        </x-button>
                    </a>
                </div>
            </div>

        </form>
    </div>
    </main>
@endsection
