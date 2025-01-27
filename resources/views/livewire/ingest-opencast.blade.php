<div>
    @if ($messageText)
        <x-message
                :messageText="$messageText"
                :messageType="$messageType" />
    @endif

    <form wire:submit="submitForm" action="#"
          class="flex flex-col"
    >
        @csrf

        <div class="flex flex-col"
             x-data="{ isUploading: false, progress: 0 }"
             x-on:livewire-upload-start="isUploading = true"
             x-on:livewire-upload-finish="isUploading = false"
             x-on:livewire-upload-error="isUploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress"
        >

            <input wire:model.live="videoFile"
                   type="file"
                   id="videoFile"
                   name="videoFile"
                   class="dark:text-white"
            >
            <p class="pt-2 text-sm italic dark:text-white">
                * {{ __('clip.backend.opencast video upload description') }}
            </p>

            <!-- Progress Bar -->
            <div class="w-full shadow-sm bg-grey" x-show="isUploading">
                <progress class="w-full text-center text-xs leading-none text-white bg-orange"
                          max="100"
                          x-bind:value="progress"></progress>
            </div>
        </div>

        <x-button class="bg-green-600 hover:bg-green-700 w-full my-2 justify-center">
            {{ __('clip.backend.actions.upload video') }}
        </x-button>

        @error('videoFile')
        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </form>
</div>
