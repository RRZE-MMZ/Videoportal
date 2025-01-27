<div
        class="mx-4 h-full w-full rounded-md border bg-white px-4 py-4 font-normal dark:bg-gray-800 dark:border-blue-800">
    <h2 class="mb-3 -ml-5 border-l-4 border-blue-600 dark:border-blue-800 py-4 pl-4 text-xl
    dark:text-white"
    >
        {{__('series.backend.Series administrator')}}
    </h2>
    <div class="flex-row">
        @if(is_null($series->owner))
            <div class="w-full pb-6 dark:text-white italic text-sm">
                {{ __('series.backend.Series has no owner yet') }}
            </div>
        @else
            <div class="text-lg  dark:text-white">
                {{$series->owner?->getFullNameAttribute().'-'.$series->owner?->username}}
            </div>
        @endif
        @can('change-series-owner')
            <div class="w-full pt-6 dark:text-white">
                <form
                        method="POST"
                        class="px-2"
                        action="{{route('series.ownership.change',$series)}}"
                >
                    @csrf

                    <div class="w-full pb-6">
                        <label>
                            <select
                                    class="p-2 w-full select2-tides-users
                                        focus:outline-hidden focus:bg-white focus:border-blue-500 "
                                    name="userID"
                                    style="width: 100%"
                            >
                            </select>
                        </label>
                    </div>
                    <x-button class="bg-blue-600 hover:bg-blue-700 dark:text-white text-sm">
                        {{ __('series.backend.update series owner') }}
                    </x-button>
                </form>
            </div>
        @endcan
    </div>

    @if($series->members()->count() > 0)
        <h4 class="border-b-2 pt-6 pb-2 dark:text-white">
            {{ trans_choice('common.menu.member', 2) }}
        </h4>
        <div class="pt-4">
            <ul class="list-disc">
                @foreach($series->members as $member)
                    <li class="mx-1 flex p-2 dark:text-white">
                        <div>
                            {{ $member->getFullNameAttribute().'/'.$member->username }}
                        </div>
                        @can('delete-series', $series)
                            <div class="pl-1">
                                <form action="{{route('series.membership.removeUser', $series)}}"
                                      method="POST">
                                    @csrf
                                    <label>
                                        <input hidden type="number"
                                               value="{{$member->id}}"
                                               name="userID" />
                                    </label>
                                    <button type="submit">
                                        <x-heroicon-o-x-circle class="h-6 w-6 text-red-500" />
                                    </button>
                                    @error('userID')
                                    <div>{{$message}}</div>
                                    @enderror
                                </form>
                            </div>
                        @endcan
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
