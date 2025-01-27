<div>
    <div x-data="{ showPicker: {{ $hasTimeAvailability ? 'true' : 'false' }},
                checked: {{ $hasTimeAvailability ? 'true' : 'false' }},
                start: '{{$timeAvailabilityStart}}',
                end: '{{$timeAvailabilityEnd}}'}"
    >
        <div class="grid grid-cols-12 content-center items-center gap-4">
            <div class="col-span-2">
                <label for="has_time_availability" class="mr-6 block py-2 font-bold text-gray-700 text-md
                dark:text-white dark:border-white ">
                    {{ __('common.metadata.time availability') }}
                </label>
            </div>
            <div>
                <div class="col-start-2 col-end-8">
                    <div class="relative h-6 w-12 select-none bg-none transition duration-200 ease-in">
                        <label for="has_time_availability" class="absolute left-0 border-2 mb-2 w-6 h-6
                            rounded-full transition transform bg-blue-500 duration-100 ease-linear cursor-pointer
                            rounded-full bg-blue-500"
                               :class="checked ? 'translate-x-full bg-blue bg-none ' : 'translate-x-0 border-gray-400'"
                        ></label>
                        <input type="checkbox" id="has_time_availability" name="has_time_availability"
                               @click="showPicker = !showPicker" class="mb-1 h-full w-full rounded-full bg-none
                            focus:outline-hidden active:outline-hidden" x-model="checked">
                    </div>
                </div>
            </div>
            @error('has_time_availability')
            <div class="col-span-8">
                <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
            </div>
            @enderror
        </div>
        <div>
            <div x-show="showPicker">
                <div class="grid grid-cols-8 items-center pt-4">
                    <div class="content-center">
                        <div class="flex flex-col">
                            <label class="ml-4 mr-2 block flex py-2 font-bold text-gray-700 text-md dark:text-white
                            dark:border-white "
                                   for="{{ $name }}_start">{{ __('common.metadata.start time') }}</label>
                        </div>
                    </div>
                    <div class="col-start-2 col-end-8">
                        <input type="datetime-local" id="{{ $name }}_start" name="{{ $name }}_start" x-model="start"
                               class="py-2 px-4 w-1/2 leading-tight text-gray-700 bg-white rounded border-2
                                            border-gray-200 appearance-none focus:outline-hidden focus:bg-white
                                            focus:border-blue-500">
                    </div>
                    @error("{$name}_start")
                    <div class="col-span-8">
                        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
                    </div>
                    @enderror
                </div>
                <div class="grid grid-cols-8 items-center py-2">
                    <div class="content-center">
                        <div class="flex flex-col">
                            <label class="ml-4 mr-6 block py-2 font-bold text-gray-700 text-md dark:text-white
                            dark:border-white "
                                   for="{{ $name }}_end">{{ __('common.metadata.end time') }}</label>
                        </div>
                    </div>
                    <div class="col-start-2 col-end-8">
                        <input type="datetime-local" id="{{ $name }}_end" name="{{ $name }}_end" x-model="end"
                               class="py-2 px-4 w-1/2 leading-tight text-gray-700 bg-white rounded border-2
                                            border-gray-200 appearance-none focus:outline-hidden focus:bg-white
                                            focus:border-blue-500">
                    </div>
                    @error("{$name}_end")
                    <div class="col-span-8">
                        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
