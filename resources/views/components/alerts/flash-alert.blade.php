@if ($message = Session::get('flashMessage'))
    <div>
        <div
            x-data="{ show: false }"
            x-init="() => {
            setTimeout(() => show = true, 0);
            setTimeout(() => show = false, 9000);
          }"
            x-show="show"
            x-description="Notification panel, show/hide based on alert state."
            @click.away="show = false"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90"
            class="mb-2 flex rounded-md bg-green-200 p-2 items-center">
            <div class="shrink-0">
                <x-heroicon-o-check-circle class="h-5 w-5 text-green-800" />
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium font-semibold leading-5 text-green-900">
                    {{ $message }}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button @click="show = false"
                            class="inline-flex rounded-md p-1.5 text-green-800 hover:bg-green-100
                                 focus:outline-hidden focus:bg-green-700 transition ease-in-out duration-150"
                            aria-label="Dismiss">
                        <x-heroicon-o-x-circle class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
