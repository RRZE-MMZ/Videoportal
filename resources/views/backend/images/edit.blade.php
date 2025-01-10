@extends('layouts.backend')

@section('content')
    <div class="mb-5 flex items-center justify-between border-b border-black pb-2 font-semibold font-2xl
                dark:text-white dark:border-white"
    >
        <div class="flex text-2xl flex-col dark:text-white font-normal">

            Edit Image ID : {{ $image->id }} / {{ $image->description }}
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
        <figure class="flex w-full rounded-xl md:p-0 dark:text-white dark:border-white font-normal">
            <div class="grow content-center justify-center px-2 py-2">
                <form action="{{ route('images.update', $image) }}"
                      method="POST"
                      class="w-full"
                >
                    @csrf
                    @method('PATCH')
                    <div class="flex flex-col gap-3">
                        <x-form.input field-name="file_name"
                                      input-type="text"
                                      :disabled="true"
                                      :value="$image->file_name"
                                      label="{{__('common.forms.file name')}}"
                                      :full-col="true"
                                      :required="true"
                        />

                        <x-form.input field-name="description"
                                      input-type="description"
                                      :value="$image->description"
                                      label="{{__('common.forms.description')}}"
                                      :full-col="true"
                                      :required="true"
                        />
                        <x-form.input field-name="mime_type"
                                      input-type="mime_type"
                                      disabled
                                      :value=" ($image->mime_type)"
                                      label="{{__('common.forms.mime type')}}"
                                      :full-col="true"
                                      :required="true"
                        />
                        <x-form.input field-name="size"
                                      input-type="size"
                                      disabled
                                      :value=" humanFileSizeFormat($image->file_size)"
                                      label="{{__('common.forms.size')}}"
                                      :full-col="true"
                                      :required="true"
                        />
                        @foreach($mediaInfoContainer->getImages() as $imageInfo)
                            @if($imageInfo->has('width'))
                                <x-form.input field-name="width"
                                              input-type="width"
                                              :disabled="true"
                                              :value=" $imageInfo->get('width')->getAbsoluteValue(). 'px'"
                                              label="{{__('common.forms.width')}}"
                                              :full-col="true"
                                              :required="true"
                                />

                            @endif
                            @if($imageInfo->has('height'))
                                <x-form.input field-name="height"
                                              input-type="height"
                                              disabled
                                              :value=" $imageInfo->get('height')->getAbsoluteValue(). 'px'"
                                              label="{{__('common.forms.height')}}"
                                              :full-col="true"
                                              :required="true"
                                />
                            @endif
                        @endforeach
                    </div>
                    <div class="pt-10">
                        <x-button class="bg-blue-600 hover:bg-blue-700">
                            {{__('common.actions.update')}}
                        </x-button>
                    </div>
                </form>
            </div>
            <div class="mr-10 flex-none flex-col align-middle items-center">
                <div>
                    <img class="h-96 w-48 w-auto object-contain rounded-[0.25rem] md:"
                         src="{{ URL::asset('/images/'.$image->file_name) }}"
                         alt="{{ $image->description }}">
                </div>
                <div class="flex">
                    <div class="w-full pt-4">
                        <form action="{{ route('images.import', $image) }}"
                              method="POST"
                              class="w-full"
                        >
                            @csrf

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
                                Replace current image
                            </x-button>

                        </form>
                    </div>
                </div>
            </div>
        </figure>
    </div>

    @if($image->presenters()->count() > 0)
        <div class="flex items-center border-b-2 border-black pt-10 dark:text-white">
            <div class="pr-2">
                <x-heroicon-o-user-group class="h-6 w-6" />
            </div>
            <h3 class="font-bold">Used in {{$image->presenters()->count()}} lecturers
            </h3>
        </div>
        <div class="flex dark:text-white">
            <div class="mt-3 w-full py-3">
                <ul>
                    @foreach($image->presenters as $presenter)
                        <li class="mt-2 mb-2 w-full p-2">
                            <div class="flex items-center align-middle">
                                <div>
                                    {{  $presenter->getFullNameAttribute() }}
                                </div>
                                <div class="pl-2">
                                    <a href="{{ route('presenters.edit', $presenter) }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if($image->series()->count() > 0)
        <div class="flex items-center border-b-2 border-black pt-10 dark:text-white">
            <div class="pr-2">
                <x-heroicon-o-user-group class="h-6 w-6" />
            </div>
            <h3 class="font-bold">Used in {{$image->series()->count()}} series
            </h3>
        </div>
        <div class="flex dark:text-white">
            <div class="mt-3 w-full py-3">
                <ul>
                    @foreach($image->series->take(5) as $clip)
                        <li class="mt-2 mb-2 w-full p-2">
                            <div class="flex items-center align-middle">
                                <div>
                                    {{  $clip->title }}
                                </div>
                                <div class="pl-2">
                                    <a href="{{ route('series.edit', $clip) }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if($image->clips()->count() > 0)
        <div class="flex items-center border-b-2 border-black pt-10 dark:text-white dark:border-white">
            <div class="pr-2">
                <x-heroicon-o-user-group class="h-6 w-6" />
            </div>
            <h3 class="font-bold">Used in {{$image->clips()->count()}} clips
            </h3>
        </div>
        <div class="flex">
            <div class="mt-3 w-full py-3">
                <ul>
                    @foreach($image->clips->take(5) as $clip)
                        <li class="mt-2 mb-2 w-full p-2 dark:text-white">
                            <div class="flex items-center align-middle">
                                <div>
                                    {{  $clip->title }}
                                </div>
                                <div class="pl-2">
                                    <a href="{{ route('clips.edit', $clip) }}">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
