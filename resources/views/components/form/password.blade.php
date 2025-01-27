<div class="grid grid-cols-8">
    <div class="content-center items-center">
        <label class="mr-6 block py-2 font-bold text-gray-700 text-md dark:text-white"
               for="{{ $fieldName }}"
        >
            {{$label}}
        </label>
    </div>
    <div class="{{($fullCol)?'col-start-2 col-end-4':'col-start-2 col-end-6'}}" x-data="{ show: true }">
        <div class="relative">
            <input placeholder=""
                   :type="show ? 'password' : 'text'"
                   value="{{$value }}"
                   name="{{ $fieldName }}"
                   id="{{ $fieldName }}"
                   class="text-md block px-3 py-2 rounded-lg w-full bg-white dark:bg-gray-200 border-2 border-gray-300
                            placeholder-gray-600 shadow-md focus:placeholder-gray-500 focus:bg-white
                            focus:border-gray-600 focus:outline-hidden"
            >
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm leading-5">
                <svg class="h-6 w-6"
                     fill="none"
                     @click="show = !show"
                     :class="{'hidden': !show, 'block':show }"
                     stroke="currentColor"
                     viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg"
                >
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                    </path>
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064
                              7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    >
                    </path>
                </svg>

                <svg class="h-6 w-6"
                     fill="none"
                     @click="show = !show"
                     :class="{'block': !show, 'hidden':show }"
                     stroke="currentColor"
                     viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg"
                >
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0
                              011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88
                              9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0
                              8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    >
                    </path>
                </svg>
            </div>
        </div>
    </div>
    @error($fieldName)
    <div class="col-start-2 col-end-6">
        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
    </div>
    @enderror
</div>
