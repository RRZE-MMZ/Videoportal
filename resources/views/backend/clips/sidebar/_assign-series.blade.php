<div class="mx-4 h-full w-full rounded-sm border bg-white px-4 py-4 font-normal dark:bg-gray-800  dark:border-blue-800">
    <h2 class="mb-3 -ml-5 border-l-4 border-blue-600 py-4 pl-4 text-xl dark:text-white">
        {{ __('clip.backend.assign a series to this clip') }}
    </h2>
    <a href="{{route('series.clips.listSeries',$clip)}}">
        <button type="button" class="items-center px-4 py-1 border border-transparent text-base leading-6
                                font-medium rounded-md text-white
                        bg-green-600  focus:shadow-outline-indigo hover:bg-green-700
                        hover:shadow-lg w-full dark:text-white">
            {{ __('clip.backend.actions.view available series') }}
        </button>
    </a>
</div>
