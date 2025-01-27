@foreach ($searchResults['series']['hits']['hits'] as $series)
    <tr class="text-sm leading-5 dark:text-white font-normal ">
        <td class="w-4/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="h-12 w-24 shrink-0">
                    <img class="h-12 w-24 "
                         src="{{ $series['_source']['poster'] }}" alt="">
                </div>
                <div class="ml-4">
                    <div class="">
                        {{ $series['_source']['title'].' |'.$series['_source']['semester'].' |  SeriesID:'.$series['_source']['id'] }}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div class="">
                        {{ $series['_source']['semester'] }}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div class="">
                        {{ $series['_source']['acls']  }}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div class="">
                        {{ ($series['_source']['organization']['org_name'])}}
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
            <div class="flex items-center">
                <div class="ml-4">
                    <div class="">
                        @if(collect($series['_source']['presenters'])->isNotEmpty())
                            <div class="flex items-center">
                                <div class="flex pr-2 items-center">
                                    <div class="pr-2">
                                        <x-heroicon-o-user class="h-4" />
                                    </div>
                                    <div class="flex items-center align-middle">
                                        @foreach ($series['_source']['presenters'] as $presenter)

                                            <div class="pr-2">
                                                {{ $presenter['presenter_fullName'] }}
                                            </div>
                                            <img
                                                src="{{ env('app_url').$presenter['presenter_image_url'] }}"
                                                alt=""
                                                class="h-8 rounded-full">
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </td>
        <td class="w-2/12 px-6 py-4 text-right text-sm font-medium leading-5 whitespace-no-wrap">
            <div class="flex space-x-2">
                <a href="{{route('series.edit',$series['_source']['slug'])}}">
                    <x-button type="button" class="bg-green-600 hover:bg-green-700">
                        {{__('common.actions.edit')}}
                    </x-button>
                </a>
            </div>
        </td>
    </tr>
@endforeach
