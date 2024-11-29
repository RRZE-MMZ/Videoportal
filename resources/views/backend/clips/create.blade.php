@use('App\Models\Semester')
@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
    dark:text-white dark:border-white">
        <div class="flex text-2xl">
            {{ __('common.heading.create new clip') }}
        </div>
    </div>
    <div class="flex px-2 py-2">
        <form action="{{ route('clips.store')}}"
              method="POST"
              class="w-4/5">
            @csrf

            <div class="flex flex-col gap-3">
                <x-form.input field-name="episode"
                              input-type="number"
                              value="1"
                              label="{{ __('common.metadata.episode') }}"
                              :full-col="false"
                              :required="false"
                />
                <x-form.datepicker field-name="recording_date"
                                   label="{{ __('common.metadata.recording date') }}"
                                   :full-col="false"
                                   :value="now()" />

                <x-form.input field-name="title"
                              input-type="text"
                              :value="old('title')"
                              label="{{ __('common.metadata.title') }}"
                              :full-col="true"
                              :required="true"
                />

                <x-form.textarea field-name="description"
                                 :value="old('description')"
                                 label="{{ __('common.metadata.description') }}"
                />

                <x-form.select2-single field-name="organization_id"
                                       label="{{ __('common.metadata.organization') }}"
                                       select-class="select2-tides-organization"
                                       model="organization"
                                       :selectedItem="(old('organization_id'))?? 1 "
                />

                <x-form.select2-single field-name="language_id"
                                       label="{{ __('common.metadata.language') }}"
                                       select-class="select2-tides"
                                       model="language"
                                       :selectedItem="old('language_id', 4)"
                />

                <div class="mb-2 border-b border-solid border-b-black pb-2 text-left text-xl font-bold
                            dark:text-white dark:border-white"
                >
                    {{ __('common.metadata.metadata') }}
                </div>

                <x-form.select2-single field-name="context_id"
                                       label="{{ __('common.metadata.context') }}"
                                       select-class="select2-tides"
                                       model="context"
                                       :selectedItem="old('context_id',0)"
                />

                <x-form.select2-single field-name="format_id"
                                       label="{{ __('common.metadata.format') }}"
                                       select-class="select2-tides"
                                       model="format"
                                       :selectedItem="old('format_id', 11)"
                />

                <x-form.select2-single field-name="type_id"
                                       label="{{ __('common.metadata.type') }}"
                                       select-class="select2-tides"
                                       model="type"
                                       :selectedItem="old('type_id',11)"
                />

                <x-form.select2-multiple field-name="presenters"
                                         label="{{trans_choice('common.menu.presenter',2)}}"
                                         select-class="select2-tides-presenters"
                                         :model="null"
                                         :items="[]"
                />

                <x-form.select2-single field-name="semester_id"
                                       label="{{ __('common.metadata.semester') }}"
                                       select-class="select2-tides"
                                       model="semester"
                                       :selectedItem="old('semester_id',Semester::current()->first()->id)"
                />

                <x-form.select2-multiple field-name="tags"
                                         label="{{ __('common.metadata.tags') }}"
                                         select-class="select2-tides-tags"
                                         :model="null"
                                         :items="[]"
                />

                <div class="mb-2 border-b border-solid border-b-black pb-2 text-left text-xl font-bold
                            dark:text-white dark:border-white"
                >
                    {{ __('common.metadata.accessible via') }}
                </div>

                <x-form.select2-multiple field-name="acls"
                                         label="{{ __('common.metadata.accessible via') }}"
                                         :model="null"
                                         select-class="select2-tides"
                />

                <x-form.password field-name="password"
                                 :value="old('password')"
                                 label="{{ __('common.metadata.password') }}"
                                 :full-col="true"
                />

                <x-form.toggle-button :value="old('allow_comments', false)"
                                      label="Allow comments"
                                      field-name="allow_comments"
                />

                <x-form.toggle-button :value="old('is_public', true)"
                                      label="Public available"
                                      field-name="is_public"
                />

                <x-form.toggle-button :value="old('is_livestream', false)"
                                      label="{{ __('common.metadata.livestream clip') }}"
                                      field-name="is_livestream"
                />

                <x-date-time-picker
                        :has-time-availability="old('has_time_availability', false)"
                        :time-availability-start="old('time_availability_start',now()->format('Y-m-d H:i'))"
                        :time-availability-end="old('time_availability_end',now()->format('Y-m-d H:i'))"
                        name="time_availability"
                        label="Time availability">
                </x-date-time-picker>

                <div class="col-span-7 w-4/5 pt-8">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        {{ __('common.heading.create new clip') }}
                    </x-button>
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
        </form>

    </div>
    <script>
      const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

      const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];


      function app() {

        return {

          showDatepicker: false,

          datepickerValue: '',


          month: '',

          year: '',

          no_of_days: [],

          blankdays: [],

          days: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],


          initDate() {

            let today = new Date();

            this.month = today.getMonth();

            this.year = today.getFullYear();

            this.datepickerValue = new Date(this.year, this.month, today.getDate()).toDateString();

          },

          isToday(date) {

            const today = new Date();

            const d = new Date(this.year, this.month, date);


            return today.toDateString() === d.toDateString() ? true : false;

          },

          getDateValue(date) {

            let selectedDate = new Date(this.year, this.month, date);

            this.datepickerValue = selectedDate.toDateString();

            this.$refs.date.value = selectedDate.getFullYear() + '-' + ('0' + selectedDate.getMonth()).slice(-2) + '-' + ('0' + selectedDate.getDate()).slice(-2);

            console.log(this.$refs.date.value);


            this.showDatepicker = false;

          },


          getNoOfDays() {

            let daysInMonth = new Date(this.year, this.month + 1, 0).getDate();


            // find where to start calendar day of week

            let dayOfWeek = new Date(this.year, this.month).getDay();

            let blankdaysArray = [];

            for (var i = 1; i <= dayOfWeek; i++) {

              blankdaysArray.push(i);

            }


            let daysArray = [];

            for (var i = 1; i <= daysInMonth; i++) {

              daysArray.push(i);

            }


            this.blankdays = blankdaysArray;

            this.no_of_days = daysArray;

          }

        };

      }
    </script>
@endsection
