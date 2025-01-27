<div id="opencast-theme-actions" class="pt-4">
    <div class="bg-green-100 text-green-800 border border-green-300 dark:bg-green-900 dark:text-green-200
dark:border-green-700 rounded-lg p-4 flex items-center space-x-3">
        <!-- Exclamation Icon -->
        <span class="text-green-600 dark:text-green-300 shrink-0">
            <x-iconoir-info-circle class="w-6 h-6 shrink-0" />
    </span>
        <!-- Info Text -->
        <div class="flex-1">
            <p class="text-md">
                {{ __('opencast.backend.themes info text') }}
            </p>
        </div>
    </div>
    <div>
        <div class="py-8">
            <form action="{{ route('series.opencast.updateSeriesTheme', $series) }}"
                  method="POST"
                  class="max-w-sm "
            >
                @method('PUT')
                @csrf
                {{-- Faculty Dropdown --}}
                <div class="form-group">
                    <label for="faculty"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                    >
                        Select Faculty:
                    </label>
                    <select id="faculty"
                            name="faculty"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                            dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                            dark:focus:border-blue-500"
                            onchange="updatePositions()"
                    >
                        <option value="">
                            Select a Faculty
                        </option>
                        @foreach ($facultiesWithPositions as $faculty)
                            <option value="{{ $faculty['faculty'] }}">
                                {{ $faculty['faculty'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Position Dropdown --}}
                <div class="form-group mt-3">
                    <label for="position"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Select watermark Position
                    </label>
                    <select id="position"
                            name="position"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                            dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                            dark:focus:border-blue-500"
                    >
                        <option value="">
                            Select a Position
                        </option>
                        {{-- Position options will be dynamically populated by JavaScript --}}
                    </select>
                </div>
                <div class="pt-8">
                    <x-button class="bg-green-700 hover:bg-green-800">
                        {{ __('common.actions.update') }}
                    </x-button>
                </div>
            </form>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const facultiesWithPositions = @json($facultiesWithPositions);
            const facultySelect = document.getElementById('faculty');
            const positionSelect = document.getElementById('position');
            const themeID = @json($themeID);

            window.updatePositions = function() {
              const selectedFaculty = facultySelect.value;

              // Clear current options
              positionSelect.innerHTML = '<option value="">Select a Position</option>';

              if (selectedFaculty) {
                const selectedFacultyData = facultiesWithPositions.find(faculty => faculty.faculty === selectedFaculty);

                if (selectedFacultyData) {
                  selectedFacultyData.positions.forEach(position => {
                    const option = document.createElement('option');
                    option.value = position.id;
                    option.textContent = position.position;

                    // Set the selected attribute if the position ID matches the themeID
                    if (position.id == themeID) {
                      option.selected = true;
                    }

                    positionSelect.appendChild(option);
                  });
                }
              }
            };

            // Trigger initial update if a themeID is provided
            if (themeID) {
              const facultyWithTheme = facultiesWithPositions.find(faculty =>
                faculty.positions.some(position => position.id == themeID)
              );

              if (facultyWithTheme) {
                facultySelect.value = facultyWithTheme.faculty;
                updatePositions();
              }
            }
          });
        </script>
    </div>
</div>
