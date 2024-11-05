<?php

namespace App\Livewire;

use App\Models\Semester;
use App\Models\Series;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class SeriesDataTable extends Component
{
    use WithPagination;

    public $userSeries = false;

    public $search;

    public $sortField = 'id';

    public $sortAsc = false;

    public $selectedSemesterID;

    public function sortBy($field): void
    {
        $this->sortAsc = ! ($this->sortField === $field) || ! $this->sortAsc;

        $this->sortField = $field;
    }

    /**
     * Updates the status of the component if search input changed
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim(Str::lower($this->search));
        $series = $this->determineClipQuery($search)->paginate(30);

        return view('livewire.series-data-table', [
            'series' => $series,
            'semestersList' => Semester::orderBy('id', 'desc')->get(),
        ]);
    }

    protected function determineClipQuery($search)
    {
        $query = $this->userSeries ? $this->userSeriesQuery($search) : $this->adminOrDefaultQuery($search);

        // Apply semester filter if a semester is selected
        if ($this->selectedSemesterID) {
            // Filter series where at least one clip has the selected semester_id
            $query->whereHas('clips', function ($q) {
                $q->where('semester_id', $this->selectedSemesterID);
            });
        }

        return $query;
    }

    protected function userSeriesQuery($search)
    {
        $query = auth()->user()->getAllSeries()->withLastPublicClip();

        $query->whereRaw('lower(title) like ?', ["%{$search}%"]);

        return $query
            ->when($this->sortField, fn ($query) => $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc'));
    }

    protected function adminOrDefaultQuery($search)
    {
        if (auth()->user()->can('administrate-assistant-pages')) {
            $query = Series::query()->withLastPublicClip();
            $query->with(['presenters'])
                ->where(function ($q) use ($search) {
                    $q->Where('id', (int) $search)
                        ->orWhereRaw('lower(title) like ?', ["%{$search}%"])
                        ->orWhere(function ($query) use ($search) {
                            $query->WhereHas('presenters', function ($q) use ($search) {
                                // Concatenate first_name and last_name and then apply the LIKE condition
                                $q->whereRaw('lower(first_name || last_name) like ?', ["%{$search}%"]);
                            });
                        });
                });
        } else {
            $query = Series::query();
            $query->with(['presenters'])
                ->withLastPublicClip()
                ->isPublic()
                ->whereHas('clips.assets')
                ->where(function ($q) use ($search) {
                    $q->orWhere('id', (int) $search)
                        ->orWhereRaw('lower(title) like ?', ["%{$search}%"])
                        ->orWhere(function ($query) use ($search) {
                            $query->whereHas('presenters', function ($q) use ($search) {
                                $q->whereRaw('lower(first_name) like ?', ["%{$search}%"])
                                    ->orWhereRaw('lower(last_name) like ?', ["%{$search}%"]);
                            })
                                ->orWhereDoesntHave('presenters');
                        });
                });
        }

        return $query
            ->when($this->sortField, fn ($query) => $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc'));
    }
}
