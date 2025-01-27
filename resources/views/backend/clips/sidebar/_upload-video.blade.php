<div class="mx-4 h-full w-full rounded-sm border bg-white px-4 py-4 font-normal dark:bg-gray-800  dark:border-blue-800">
    <h2 class="mb-3 -ml-5 border-l-4 border-blue-600 py-4 pl-4 text-xl dark:text-white">
        {{ __('clip.backend.upload a video') }}
    </h2>
    <form action="{{ route('admin.clips.asset.transferSingle',$clip) }}"
          enctype="multipart/form-data"
          method="POST"
          class="flex flex-col"
    >
        @csrf
        <input type="file" id="asset" name="asset" class="dark:text-white">

        <x-button class="bg-green-600 hover:bg-green-700 w-full mt-6 justify-center">
            {{ __('clip.backend.actions.upload video') }}
        </x-button>

        @error('asset')
        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </form>
</div>
