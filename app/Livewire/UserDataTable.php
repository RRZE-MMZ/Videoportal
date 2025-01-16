<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class UserDataTable extends Component
{
    use WithPagination;

    public $admin = false;

    public $search;

    public $sortField = 'logged_in_at';

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
     * Updates the status of the component if  admin checkbox changed
     */
    public function updatingAdmin(): void
    {
        $this->resetPage();
    }

    /**
     * Render Livewire component
     */
    public function render(): View
    {
        $search = trim(Str::lower($this->search));

        return view('livewire.user-data-table', [
            'users' => ($this->admin)
                ? User::admins()
                    ->where(function ($query) use ($search) {
                        $query->whereRaw('lower(first_name) like (?)', ["%{$search}%"])
                            ->orwhereRaw('lower(last_name) like (?)', ["%{$search}%"])
                            ->orwhereRaw('lower(username) like (?)', ["%{$search}%"])
                            ->orwhereRaw('lower(email) like (?)', ["%{$search}%"])
                            ->orWhereHas('roles', function ($q) use ($search) {
                                $q->whereRaw('lower(name)  like (?)', ["%{$search}%"]);
                            });
                    })
                    ->when($this->sortField, function ($query) {
                        $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                    })->with(['roles'])
                    ->paginate(10)
                : User::search($search)
                    ->orWhereHas('roles', function ($q) use ($search) {
                        $q->whereRaw('lower(name)  like (?)', ["%{$search}%"]);
                    })
                    ->when($this->sortField, function ($query) {
                        $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
                    })->with(['roles'])
                    ->paginate(10),
        ]);
    }
}
