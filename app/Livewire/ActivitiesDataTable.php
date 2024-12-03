<?php

namespace App\Livewire;

use App\Models\Activity;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ActivitiesDataTable extends Component
{
    use WithPagination;

    public $series = false;

    public $model = '';

    public $objectID = 1;

    public $search = '';

    public $sortField;

    public $sortAsc = true;

    /**
     * Sort users by method parameter
     */
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

    /**
     * Updates the status of the component if series checkbox changed
     */
    public function updatingSeries(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $activities = match ($this->model) {
            'series' => call_user_func(function () {
                return Activity::where('content_type', 'series')
                    ->where('object_id', $this->objectID)
                    ->where(function ($query) {
                        $query->search($this->search);
                    })->when($this->sortField, function ($query) {
                        $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                    })
                    ->orderBy('id', 'desc')
                    ->paginate(20);
            }),
            'clip' => call_user_func(function () {
                return Activity::where('content_type', 'clip')
                    ->where('object_id', $this->objectID)
                    ->where(function ($query) {
                        $query->search($this->search);
                    })->when($this->sortField, function ($query) {
                        $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                    })
                    ->orderBy('id', 'desc')
                    ->paginate(20);
            }),
            'podcast' => call_user_func(function () {
                return Activity::where('content_type', 'podcast')
                    ->where('object_id', $this->objectID)
                    ->where(function ($query) {
                        $query->search($this->search);
                    })->when($this->sortField, function ($query) {
                        $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                    })
                    ->orderBy('id', 'desc')
                    ->paginate(20);
            }),
            'podcastEpisode' => call_user_func(function () {
                return Activity::where('content_type', 'podcastEpisode')
                    ->where('object_id', $this->objectID)
                    ->where(function ($query) {
                        $query->search($this->search);
                    })->when($this->sortField, function ($query) {
                        $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                    })
                    ->orderBy('id', 'desc')
                    ->paginate(20);
            }),
            default => call_user_func(function () {
                return ($this->series)
                     ? Activity::where('content_type', 'series')
                         ->where(function ($query) {
                             $query->search($this->search);
                         })->when($this->sortField, function ($query) {
                             $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                         })
                         ->orderBy('id', 'desc')
                         ->paginate(20)
                     : Activity::search($this->search)
                         ->when($this->sortField, function ($query) {
                             $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                         })
                         ->orderBy('id', 'desc')
                         ->paginate(20);
            })
        };

        return view('livewire.activities-data-table', [
            'activities' => $activities,
        ]);
    }
}
