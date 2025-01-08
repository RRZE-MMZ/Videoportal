<div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-4 py-2">
    <!-- Label Section -->
    <div class="{{ $labelClass }} sm:col-span-4">
        <label for="{{ $fieldName }}"
               class="block py-2 font-bold text-gray-700 text-md dark:text-white">
            {{ $label }}
        </label>
    </div>

    <!-- Toggle Button Section -->
    <div class="sm:col-span-8">
        <div class="w-full bg-none" x-data="{ checked: {{ $value ? 'true' : 'false' }} }">
            <div class="relative inline-block w-12 h-6 rounded-full transition duration-200 ease-in"
                 :class="checked ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'">
                <!-- Toggle Dot -->
                <label for="{{ $fieldName }}"
                       class="absolute left-0 w-6 h-6 bg-white border-2 rounded-full transition transform cursor-pointer
                              duration-100 ease-linear"
                       :class="checked ? 'translate-x-full border-blue-500' : 'translate-x-0 border-gray-400'">
                </label>
                <!-- Hidden Checkbox -->
                <input type="checkbox" id="{{ $fieldName }}" name="{{ $fieldName }}"
                       class="sr-only" x-model="checked">
            </div>
        </div>
    </div>

    <!-- Error Message -->
    @error($fieldName)
    <div class="col-span-1 sm:col-span-8">
        <p class="mt-2 w-full text-xs text-red-500">{{ $message }}</p>
    </div>
    @enderror
</div>
