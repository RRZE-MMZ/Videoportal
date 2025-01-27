@unless ($breadcrumbs->isEmpty())
    <nav class="bg-white dark:bg-gray-800 p-2 sm:p-4 rounded-lg shadow-xs">
        <ol class="flex flex-wrap items-center text-sm sm:text-base text-gray-600 dark:text-gray-300 space-x-1 sm:space-x-2">
            @foreach ($breadcrumbs as $breadcrumb)
                @if (!is_null($breadcrumb->url) && !$loop->last)
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb->url }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            {{ $breadcrumb->title }}
                        </a>
                        <span class="text-gray-500 dark:text-gray-400 mx-1">/</span>
                    </li>
                @else
                    <li class="breadcrumb-item active text-gray-700 dark:text-gray-200">
                        {{ $breadcrumb->title }}
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endunless
