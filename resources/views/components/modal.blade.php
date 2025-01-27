<div {{ $attributes }}
     x-data="{ show: @entangle($attributes->wire('model')).live }"
     x-show="show"
     @keydown.escape.window="show = false"
     x-on:click.away="show = false"
     style="display:none"
>
    <div
        class="absolute inset-0 m-auto h-64 max-w-full sm:max-w-(--breakpoint-sm) md:max-w-(--breakpoint-md) lg:max-w-(--breakpoint-lg)
        xl:max-w-(--breakpoint-xl) 2xl:max-w-(--breakpoint-2xl) rounded-md bg-white p-4 shadow-md dark:bg-slate-800">
        <div class="flex h-full flex-col justify-between">
            <header class="flex justify-center">
                <h3 class="text-center text-lg font-bold dark:text-white">
                    {{ $title }}
                </h3>
            </header>
            <main class="mb-4 flex justify-center dark:text-black">
                {{ $body }}
            </main>
            <footer class="flex justify-center">
                {{ $footer  }}
            </footer>
        </div>
    </div>
</div>

