<div class="flex mt-6  pb-1  mb-5 font-medium border-b border-black font-3xl dark:text-white dark:border-white">
    {{ __('chapter.backend.add clips to chapter') }}
</div>
<form action="{{ route('series.chapters.addClips',[$series,$chapter]) }}"
      method="POST"
      class="flex"
>
    @method('PATCH')
    @csrf
    <div class=" flex-row w-full">
        <div class="w-full">
            <select name="ids[]"
                    class="select2-tides px-4 py-4 h-4 w-full focus:outline-hidden focus:bg-white focus:border-blue-500"
                    multiple
                    style="width:100%">
                @foreach ($series->clipsWithoutChapter($chapter) as $clip)
                    <option value="{{$clip->id}}"> {{$clip->episode}} - {{$clip->title}} / <span
                                class="text-sm">{{ $clip->recording_date }}</span>
                    </option>
                @endforeach

            </select>
            @error('ids')
            <div class="col-start-2 col-end-6">
                <p class="mt-2 w-full text-xs text-red-500"
                >
                    {{ $message }}
                </p>
            </div>
            @enderror
        </div>
        <div class="pt-8">
            <x-button class="bg-blue-600 hover:bg-blue-700"
                      type="submit"
            >
                {{ __('chapter.backend.actions.add selected clips to chapter') }}
            </x-button>
        </div>
    </div>
</form>
