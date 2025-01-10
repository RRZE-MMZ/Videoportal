@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl">
        <div class="flex">
            {{ __('image.backend.actions.create a new image') }}
        </div>
        <div class="flex">
            <a href="{{ route('images.index') }}">
                <x-button class="flex items-center bg-blue-700 hover:bg-blue-700">
                    <div class="pr-2">
                        <x-heroicon-o-arrow-left-circle class="h-6 w-6" />
                    </div>
                    <div>
                        {{ __('image.backend.actions.back to images list') }}
                    </div>
                </x-button>
            </a>
        </div>
    </div>
    <div class="flex">
        <form action="{{ route('images.store') }}"
              method="POST"
              class="w-full"
        >
            @csrf
            <x-form.input field-name="description"
                          input-type="description"
                          :value="'image description'"
                          label="{{__('common.forms.description')}}"
                          :full-col="true"
                          :required="true"
            />

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
            <x-button class="bg-blue-600 hover:bg-blue-700">
                <div class="flex">
                    <x-heroicon-o-arrow-up-circle class="h-6 w-6" />
                    <span class="pl-4">
                        {{ __('common.actions.upload file') }}
                    </span>
                </div>
            </x-button>

        </form>
    </div>
@endsection
