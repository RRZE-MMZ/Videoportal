@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
    >
        Edit device: {{ $device->name }}
    </div>
    <div class="flex px-2 py-2">
        <form action="{{ route('devices.update',$device) }}"
              method="POST" class="w-4/5">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-3">
                <x-form.input field-name="name"
                              input-type="text"
                              :value="old('name', $device->name)"
                              label="Device name"
                              :full-col="true"
                              :required="true"
                />

                <x-form.select2-single field-name="location_id"
                                       label="Location"
                                       select-class="select2-tides"
                                       model="location"
                                       :selectedItem="$device->location_id"
                />

                <x-form.select2-single field-name="organization_id"
                                       label="Organization"
                                       select-class="select2-tides-organization"
                                       model="organization"
                                       :selectedItem="(old('organization_id', $device->organization_id))?? 1 "
                />

                <x-form.input field-name="opencast_device_name"
                              input-type="text"
                              :value="old('opencast_device_name', $device->opencast_device_name)"
                              label="Opencast device name"
                              :full-col="true"
                              :required="false"
                />

                <x-form.input field-name="url"
                              input-type="url"
                              :value="old('url', $device->url)"
                              label="URL"
                              :full-col="true"
                              :required="false"
                />

                <x-form.input field-name="room_url"
                              input-type="url"
                              :value="old('room_url', $device->room_url)"
                              label="Room URL"
                              :full-col="true"
                              :required="false"
                />

                <x-form.input field-name="camera_url"
                              input-type="url"
                              :value="old('camera_url', $device->camera_url)"
                              label="Camera URL"
                              :full-col="true"
                              :required="false"
                />

                <x-form.input field-name="power_outlet_url"
                              input-type="url"
                              :value="old('power_outlet_url', $device->power_outlet_url)"
                              label="Power outlet URL"
                              :full-col="true"
                              :required="false"
                />

                <x-form.input field-name="ip_address"
                              input-type="ip"
                              :value="old('ip_address',$device->ip_address)"
                              label="IP Address"
                              :full-col="true"
                              :required="false"
                />

                <x-form.toggle-button :value="$device->has_recording_func"
                                      label="Recording available"
                                      field-name="has_recording_func"
                />
                <x-form.toggle-button :value="$device->has_livestream_func"
                                      label="Livestream available"
                                      field-name="has_livestream_func"
                />
                <x-form.toggle-button :value="$device->is_hybrid"
                                      label="Hybrid"
                                      field-name="is_hybrid"
                />
                <x-form.toggle-button :value="$device->operational"
                                      label="Operational"
                                      field-name="operational"
                />

                <x-form.textarea field-name="description"
                                 :value="old('description', $device->description)"
                                 label="Description"
                />

                <x-form.textarea field-name="comment2"
                                 :value="old('comment', $device->comment)"
                                 label="Comment"
                />

                <x-form.input field-name="telephone_number"
                              input-type="number"
                              :value="old('telephone_number',$device->telephone_number)"
                              label="Telephone number"
                              :full-col="true"
                              :required="false"
                />

                <div class="col-span-7 mt-10 w-4/5 space-x-4">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        Update device
                    </x-button>
                    <a href="{{route('devices.index')}}">
                        <x-button type="button" class="bg-gray-600 hover:bg-gray:700">
                            Back to devices list
                        </x-button>
                    </a>
                </div>
            </div>

        </form>
@endsection
