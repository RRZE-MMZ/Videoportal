<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class SearchUsersDropdown extends Component
{
    public $search = '';

    public $users = [];

    public $selectedUserId = null;

    public $selectedUser = null;

    public $activeIndex = 0;

    public function updatedSearch()
    {
        // Only perform the search if there are at least 2 characters
        if (strlen($this->search) >= 2) {
            $this->users = User::Moderators()->search($this->search)
                ->limit(10)
                ->get();
        } else {
            $this->users = []; // Clear results if search is too short
        }

        $this->activeIndex = 0; // Reset selection when new search starts
    }

    public function selectUser($userId = null)
    {
        if ($userId === null && isset($this->users[$this->activeIndex])) {
            $userId = $this->users[$this->activeIndex]->id;
        }

        if ($userId) {
            $this->selectedUser = User::find($userId);
            $this->selectedUserId = $this->selectedUser->id;
            $this->search = $this->selectedUser->getFullNameAttribute().'['.$this->selectedUser->username.']'; // Show selected user's name in input

            $this->dispatch('userSelected', $this->selectedUserId);

            $this->users = []; // Hide dropdown
        }
    }

    public function moveSelection($direction)
    {
        if ($direction === 'up') {
            $this->activeIndex = $this->activeIndex > 0 ? $this->activeIndex - 1 : count($this->users) - 1;
        } elseif ($direction === 'down') {
            $this->activeIndex = $this->activeIndex < count($this->users) - 1 ? $this->activeIndex + 1 : 0;
        }

        // Emit an event to scroll into view
        $this->dispatch('scrollToActiveItem', $this->activeIndex);
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->selectedUserId = null;
        $this->users = [];
        $this->selectedUser = null;
    }

    public function render()
    {
        return view('livewire.search-users-dropdown');
    }
}
