<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class DeleteModalWindow extends Component
{
    use AuthorizesRequests;

    public $model;

    public $showModal = false;

    public function render(): View|Application|Factory
    {
        return view('livewire.delete-modal-window');
    }

    public function delete(): RedirectResponse|Redirector
    {
        // Only portal admins can delete images.
        $this->authorize('administrate-admin-portal-pages');

        Storage::disk('images')->delete($this->model->file_name);
        Storage::disk('images')->delete('Thumbnails/'.$this->model->file_name);
        $this->model->delete();

        return to_route('images.index');
    }
}
