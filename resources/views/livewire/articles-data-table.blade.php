<div class="flex flex-col">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="w-full max-w-lg lg:max-w-xs">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                        </div>
                        <input wire:model.live="search"
                               id="search"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5
                                            bg-white placeholder-gray-500 focus:outline-hidden focus:placeholder-gray-400
                                            focus:border-blue-300 focus:shadow-outline-blue sm:text-sm transition
                                            duration-150 ease-in-out"
                               placeholder="Search" type="search">
                    </div>
                </div>
            </div>

            <div class="mt-4 overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('name')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Title [DE]
                                </button>
                                <x-sort-icon
                                    field="title_de"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('faculty')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Title [EN]
                                </button>
                                <x-sort-icon
                                    field="title_en"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('location')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Content Preview [DE]
                                </button>
                                <x-sort-icon
                                    field="content_de"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <button wire:click="sortBy('camera')"
                                        class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Content Preview [EN]
                                </button>
                                <x-sort-icon
                                    field="content_en"
                                    :sortField="$sortField"
                                    :sortAsc="$sortAsc" />
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Published
                                </div>
                            </div>
                        </th>
                        <th
                            class="px-6 py-3 text-left">
                            <div class="flex items-center">
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 text-xs dark:text-white leading-4 font-medium
                                           text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </div>
                            </div>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 ">

                    @foreach ($articles as $article)
                        <tr class="leading-5 text-gray-900  dark:text-white  font-normal">
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $article->title_de }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ $article->title_en }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-4/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ str()->of(removeHtmlElements($article->content_de))->limit(150, ' (...)')  }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-4/12 px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="">
                                            {{ str()->of(removeHtmlElements($article->content_en))->limit(150, ' (...)')  }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm">
                                    @if ($article->is_published)
                                        <x-heroicon-o-check-badge class="h-6 w-6" />
                                    @else
                                        <x-heroicon-o-x-circle class="h-6 w-6" />
                                    @endif
                                </div>
                            </td>
                            <td class="w-2/12 px-6 py-4 text-right text-sm font-medium leading-5 whitespace-no-wrap">
                                <div class="flex space-x-2">
                                    <a href="{{route('articles.edit',$article)}}">
                                        <x-button type="button" class="bg-green-600 hover:bg-green-700">
                                            {{__('common.actions.edit')}}
                                        </x-button>
                                    </a>
                                    <x-modals.delete
                                        :route="route('articles.destroy', $article)"
                                        class="w-full justify-center"
                                    >
                                        <x-slot:title>
                                            {{ __('article.backend.delete.modal title',[
                                            'article_title'=>$article->title_de
                                            ]) }}
                                        </x-slot:title>
                                        <x-slot:body>
                                            {{ __('article.backend.delete.modal body') }}
                                        </x-slot:body>
                                    </x-modals.delete>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
    <div class="h-96"></div>
</div>

