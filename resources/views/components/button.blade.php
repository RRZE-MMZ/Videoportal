@props(['tooltip' => false, 'tooltipText' =>'Default text'])
<button
    @if($tooltip)
        x-data="{ tooltip: false }"
    x-on:mouseover="tooltip = true"
    x-on:mouseleave="tooltip = false"
    @endif
    {{ $attributes->merge(['type' => 'submit', 'class' => 'relative inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium text-base text-white tracking-wider active:bg-white-900 focus:outline-hidden focus:border-white-900 focus:ring-3 ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
    @if($tooltip)
        <div x-show="tooltip"
             class="text-sm text-white absolute bg-blue-400 rounded-lg p-2 transform -translate-y-full
             -translate-x-1/2 left-1/2 z-10"
             style="white-space: nowrap; margin-bottom: 0.5rem;">
            {{ $tooltipText }}
        </div>
    @endif
</button>
