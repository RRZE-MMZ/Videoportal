<div class="relative w-full py-8" x-data="{ isOpen: false }" @click.away="isOpen = false">
    <!-- Search Input -->
    <input
            type="text"
            wire:model.live.debounce.10ms="search"
            placeholder="Search users..."
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @focus="isOpen = true"
            @keydown.arrow-down.prevent="$wire.moveSelection('down')"
            @keydown.arrow-up.prevent="$wire.moveSelection('up')"
            @keydown.enter.prevent="$wire.selectUser()"
    />

    <!-- Dropdown Results -->
    @if (!empty($users))
        <ul
                class="absolute w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg
                max-h-92 overflow-y-auto z-10"
                x-show="isOpen"
                x-cloak
        >
            @foreach($users as $index => $user)
                <li
                        wire:click="selectUser({{ $user->id }})"
                        class="px-4 py-2 cursor-pointer hover:bg-blue-100 transition duration-150"
                        :class="{ 'bg-blue-200': {{ $index }} === $wire.activeIndex }"
                >
                    <span class="font-semibold text-gray-800">{{ $user->getFullNameAttribute() }}</span>
                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                </li>
            @endforeach
        </ul>
    @endif

    <!-- No Results Found - Hidden After Selection -->
    @if (strlen($search) > 1 && empty($users) && !$selectedUser)
        <div class="absolute w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-3 text-center text-gray-500">
            No user found.
        </div>
    @endif
</div>

<script>
  document.addEventListener('livewire:load', function() {
    Livewire.on('scrollToActiveItem', (activeIndex) => {
      let dropdown = document.querySelector('[x-ref="dropdownList"]');
      let activeItem = document.querySelector(`[x-ref="dropdownItem${activeIndex}"]`);

      if (dropdown && activeItem) {
        let dropdownRect = dropdown.getBoundingClientRect();
        let activeItemRect = activeItem.getBoundingClientRect();

        // Check if the active item is out of view and scroll if needed
        if (activeItemRect.top < dropdownRect.top) {
          dropdown.scrollTop -= (dropdownRect.top - activeItemRect.top);
        } else if (activeItemRect.bottom > dropdownRect.bottom) {
          dropdown.scrollTop += (activeItemRect.bottom - dropdownRect.bottom);
        }
      }
    });
  });
</script>