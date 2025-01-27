<div id="image"
     class="mx-4 h-full w-full rounded border bg-white px-4 py-4 dark:bg-gray-800
           dark:border-blue-800 dark:text-white  font-normal"
>
    <h2 class="mb-3 -ml-5 border-l-4 border-blue-600 py-4 pl-4 text-xl">
        {{ str($type)->ucfirst() }} {{ trans_choice('common.menu.image',1) }}
    </h2>

    <div class="">
        <img src="{{  asset('images/'.$model->image?->file_name) }}" alt="{{ $model->image?->description }}">
    </div>

    <div class="flex w-full pt-4">
        @if ($model->image?->id != (int)config('settings.portal.default_image_id'))
            <div>
                <form
                        method="POST"
                        class="w-full px-2 pt-4"
                        action="{{ route('update.'.$type.'.image', $model) }}"
                >
                    @method('PUT')
                    @csrf
                    <input type="number"
                           hidden="hidden"
                           name="imageID"
                           readonly
                           value="{{config('settings.portal.default_image_id')}}">
                    <x-button class="bg-blue-600 hover:bg-blue-700">
                        {{ __('image.backend.Set default image') }}
                    </x-button>
                </form>
            </div>
        @endif
    </div>

    <div class="flex pt-3">
        <form
                method="POST"
                class="w-full px-2 pt-4"
                action="{{ route('update.'.$type.'.image', $model) }}"
        >
            @method('PUT')
            @csrf

            <div class="flex flex-col">
                <div class="w-full">
                    <select
                            class="w-full select2-tides-images focus:border-blue-500 focus:bg-white focus:outline-hidden"
                            name="imageID"
                            style="width: 100%"
                    >
                    </select>
                    @error('imageID')
                    <div class="mt-2 text-red-600">{{ $message }}</div>
                    @enderror
                </div>
                @if($type === 'series')
                    <div class="flex items-center py-6">
                        <div class="pr-2">
                            <label for="assignClips">
                                {{ __('image.backend.Overwrite image in clips') }}
                            </label>
                        </div>
                        <x-checkbox name="assignClips" label="Override clips also"></x-checkbox>
                    </div>
                @endif
            </div>

            <x-button class="mt-3 bg-blue-600 hover:bg-blue-700">
                {{ __('image.backend.Assign selected image') }}
            </x-button>
        </form>
    </div>
</div>
