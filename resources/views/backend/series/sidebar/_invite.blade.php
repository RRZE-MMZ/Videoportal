<div
        class="mx-4 h-full w-full rounded border bg-white  dark:text-white px-4 py-4 dark:bg-gray-800
           dark:border-blue-800"
>
    <h2 class="mb-3 -ml-5 border-l-4 border-blue-600 py-4 pl-4 text-xl font-normal">
        {{ __('series.backend.Add a series member')  }}
    </h2>

    <form
            method="POST"
            class="px-2"
            action="{{route('series.membership.addUser',$series)}}"
    >
        @csrf

        <div class="w-full pb-6">
            <select
                    class="w-full p-2 select2-tides-users focus:border-blue-500 dark:bg-gray-200 focus:bg-white focus:outline-hidden"
                    name="userID"
                    style="width: 100%"
            >
            </select>
        </div>
        <x-button class="bg-blue-600 hover:bg-blue-700">
            {{ __('common.actions.update') }}
        </x-button>
    </form>
</div>
