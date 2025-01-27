<a href="{{ $url ?? url()->previous() }}" {{ $attributes->merge([ 'class' => 'inline-flex items-center px-4 py-2  border
border-transparent rounded-md font-medium text-base text-white tracking-wider
active:bg-white-900 focus:outline-hidden focus:border-white-900 focus:ring-3 ring-gray-300 disabled:opacity-25
transition ease-in-out duration-150']) }}>
    {{ $slot }}
</a>
