<div class="flex w-full pt-4">
    <button wire:click="{{ $formAction }}"
            class="flex px-4 py-2 bg-gray-800 border border-transparent rounded-md
                    font-semibold text-xs text-white uppercase tracking-widest
                    hover:bg-gray-700 active:bg-gray-900 focus:outline-hidden focus:border-gray-900
                    focus:ring-3 ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
        {{ $btnText }}
        <x-heroicon-o-heart class="ml-4 h-4 w-4 fill-white" />
    </button>
</div>
