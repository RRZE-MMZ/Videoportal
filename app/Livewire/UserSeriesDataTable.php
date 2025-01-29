<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class UserSeriesDataTable extends Component
{
    use WithPagination;

    public $search;

    public $user;

    public $selectedUserId; // Store selected user ID from search users dropdown component

    protected $listeners = ['userSelected' => 'setUserId'];

    public function setUserId($userId)
    {
        $this->selectedUserId = $userId;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim(Str::lower($this->search));
        $series = $this->user->series()->withLastPublicClip()
            ->where(function ($q) use ($search) {
                $q->Where('id', (int) $search)
                    ->orWhereRaw('lower(title) like ?', ["%{$search}%"])
                    ->orWhere(function ($query) use ($search) {
                        $query->WhereHas('presenters', function ($q) use ($search) {
                            // Concatenate first_name and last_name and then apply the LIKE condition
                            $q->whereRaw('lower(first_name || last_name) like ?', ["%{$search}%"]);
                        });
                    });
            })->orderBy('id', 'desc')->paginate(50);

        return view('livewire.user-series-data-table')->with([
            'series' => $series,
            'selectedUserId' => $this->selectedUserId,
        ]);
    }
}
