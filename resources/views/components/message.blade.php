<div>
    <div
        x-data="{ show: false }"
        x-init="@this.on('updated',() => {
                setTimeout(() => show = true, 0);
                setTimeout(() => show = false, 2000);
              })"
        x-show="show"
        x-description="Notification panel, show/hide based on alert state."
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="flex rounded-md bg-{{ ($messageType=='success')?'green':'red' }}-200 p-4 my-8">
            <div class="shrink-0">
                <svg class="h-5 w-5 text-{{ ($messageType=='success')?'green':'red' }}-400"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707
                          9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm leading-5 font-medium text-{{ ($messageType=='success')?'green':'red' }}-800">
                    {{ $messageText }}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button @click="show = false" type="button"
                            class="inline-flex rounded-md p-1.5 text-{{ ($messageType=='success')?'green':'red' }}-500
                                    hover:bg-{{ ($messageType=='success')?'green':'red' }}-100 focus:outline-hidden
                                    focus:bg-{{ ($messageType=='success')?'green':'red' }}-100 transition
                                    ease-in-out duration-150"
                            aria-label="Dismiss"
                    >
                        <svg class="h-5 w-5"
                             viewBox="0 0 20 20"
                             fill="currentColor"
                        >
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414
                                  10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586
                                   10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
</div>
