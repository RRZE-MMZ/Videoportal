@use(App\Models\Semester)

@extends('layouts.backend')

@section('content')
    <div class="flex border-b border-black text-2xl flex-col dark:text-white dark:border-white font-normal">
        Add clip to {{ $series->title }}
    </div>
    <div class="flex px-2 py-4">
        <form action="{{route('series.clips.store', $series)}}"
              method="POST"
              class="w-4/5">
            @csrf

            <div class="flex flex-col gap-3">

                <x-form.input field-name="episode"
                              input-type="number"
                              :value="$series->latestClip?->episode + 1"
                              label="Episode"
                              :full-col="false"
                              :required="false"
                />

                <x-form.datepicker field-name="recording_date"
                                   label="Recording Date"
                                   :full-col="false"
                                   :value="now()" />


                <x-form.input field-name="title"
                              input-type="text"
                              :value="$series->latestClip?->title"
                              label="Title"
                              :full-col="true"
                              :required="true"
                />

                @if($series->chapters()->count()> 0)
                    <x-form.select2-single field-name="chapter_id"
                                           label="Chapter"
                                           select-class="select2-tides"
                                           model="chapter"
                                           :where-i-d="$series->id"
                                           :selectedItem="$series->latestClip->chapter_id"
                    />
                @endif
                <x-form.textarea field-name="description"
                                 :value="old('description')"
                                 label="Description"
                />

                <x-form.select2-single field-name="organization_id"
                                       label="Organization"
                                       select-class="select2-tides-organization"
                                       model="organization"
                                       :selectedItem="($series->latestClip?->organization_id) ?? $series->organization_id"
                />

                <x-form.select2-single field-name="language_id"
                                       label="Language"
                                       select-class="select2-tides"
                                       model="language"
                                       :selectedItem="$series->latestClip?->language_id"
                />

                <div class="mb-2 border-b border-solid border-b-black pb-2 text-left text-xl font-bold
                            dark:text-white dark:border-white"
                >
                    Metadata
                </div>

                <x-form.select2-single field-name="context_id"
                                       label="Context"
                                       select-class="select2-tides"
                                       model="context"
                                       :selectedItem="($series->latestClip?->context_id) ?? 22"
                />

                <x-form.select2-single field-name="format_id"
                                       label="Format"
                                       select-class="select2-tides"
                                       model="format"
                                       :selectedItem="($series->latestClip?->format_id) ?? 11"
                />

                <x-form.select2-single field-name="type_id"
                                       label="Type"
                                       select-class="select2-tides"
                                       model="type"
                                       :selectedItem="($series->latestClip?->type_id) ?? 11"
                />

                <x-form.select2-multiple field-name="presenters"
                                         label="Presenters"
                                         select-class="select2-tides-presenters"
                                         :model="$series->latestClip"
                                         :items="$series->presenters"
                />

                <x-form.select2-single field-name="semester_id"
                                       label="Semester"
                                       select-class="select2-tides"
                                       model="semester"
                                       :selectedItem="$series->latestClip?->semeseter_id ?? Semester::current()->first()->id"
                />

                <x-form.select2-multiple field-name="tags"
                                         label="Tags"
                                         select-class="select2-tides-tags"
                                         :model="null"
                                         :items="[]"
                />

                <div class="mb-2 border-b border-solid border-b-black pb-2 text-left text-xl font-bold
                            dark:text-white dark:border-white"
                >
                    Access
                </div>
                <x-form.select2-multiple field-name="acls"
                                         label="Accessible via"
                                         :model="$series->latestClip"
                                         select-class="select2-tides"
                />

                <x-form.password field-name="password"
                                 :value="$series->password"
                                 label="Password"
                                 :full-col="true"
                />

                <x-form.toggle-button :value="$series->allow_comments"
                                      label="Allow comments"
                                      field-name="allow_comments"
                />

                <x-form.toggle-button :value="$series->is_public"
                                      label="Public available"
                                      field-name="is_public"
                />

            </div>
            <div class="pt-10">
                <x-form.button :link="$link=false" type="submit" text="Add a Clip to Series" />
            </div>
        </form>
    </div>
    </main>
@endsection
