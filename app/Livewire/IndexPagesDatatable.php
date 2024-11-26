<?php

namespace App\Livewire;

use App\Models\Clip;
use App\Models\Series;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPagesDatatable extends Component
{
    use WithPagination;

    public $search;

    public $sortField = 'id';

    public $sortAsc = true;

    public $type = 'series';

    public $singleClips = true;

    public $organization;

    public $actionButton = 'edit';

    public $actionObj;

    public function sortBy($field): void
    {
        $this->sortAsc = ($this->sortField !== $field) || ! $this->sortAsc;
        $this->sortField = $field;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim(Str::lower($this->search));
        $objects = $this->determineQuery($search)->paginate(20);

        return view('livewire.index-pages-datatable', [
            'type' => $this->type,
            'objs' => $objects,
            'search' => $search,
            'actionButton' => $this->actionButton,
        ]);
    }

    protected function determineQuery($search)
    {
        switch ($this->type) {
            case 'series':
                return $this->querySeries($search);
            case 'clips':
                return $this->queryClips($search);
            case 'organization':
                return $this->queryOrganization($search);
            default:
                return collect(); // Return empty collection as fallback
        }
    }

    protected function querySeries($search)
    {
        $query = Series::query()->with(['presenters'])->withLastPublicClip();

        if ($this->actionButton === 'dashboard') {
            $query = $this->userSeriesQuery(query: $query, currentSemester: true);
        } elseif ($this->actionButton === 'assignClip' && ! $this->isAdmin()) {
            $query = $this->userSeriesQuery($query);
        } else {
            $query->isPublic()->whereHas('clips.assets');
        }

        return $this->applySearchFilter($query, $search)->orderBy('id', 'desc');
    }

    protected function queryClips($search)
    {
        $query = Clip::query()->with(['presenters'])->Public();

        if ($this->actionButton === 'dashboard') {
            return auth()->user()
                ->clips()
                ->single()
                ->with(['presenters'])
                ->currentSemester();
        }
        if ($this->singleClips) {
            $query->Single();
        }

        return $this->applySearchFilter($query, $search)->orderBy('updated_at', 'desc');
    }

    protected function queryOrganization($search)
    {
        $string = Str::substr($this->organization->orgno, 0, 2);

        $query = Series::whereHas('organization', function ($q) use ($string) {
            $q->whereRaw('orgno like ?', ["{$string}%"]);
        })->with(['presenters'])
            ->withLastPublicClip()
            ->isPublic()
            ->whereHas('clips.assets');

        return $this->applySearchFilter($query, $search)->orderBy('id', 'desc');
    }

    protected function userSeriesQuery($query, $currentSemester = false)
    {
        if ($currentSemester) {
            return auth()->user()->getAllSeries()->with(['presenters'])
                ->withLastPublicClip()->currentSemester();
        }

        if ($this->isAdmin()) {
            return $query;
        }

        return auth()->user()->getAllSeries()->with(['presenters'])->withLastPublicClip();
    }

    protected function applySearchFilter($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('id', (int) $search)
                ->orWhereRaw('lower(title) like ?', ["%{$search}%"])
                ->orWhereHas('presenters', function ($q) use ($search) {
                    $this->applyPresenterSearchFilter($q, $search);
                });
        });
    }

    protected function applyPresenterSearchFilter($query, $search)
    {
        if (DB::getDriverName() === 'pgsql' || DB::getDriverName() === 'sqlite') {
            $query->whereRaw('lower(first_name) like ?', ["%{$search}%"])
                ->orWhereRaw('lower(last_name) like ?', ["%{$search}%"])
                ->orWhereRaw('lower(first_name || \' \' || last_name) like ?', ["%{$search}%"])
                ->orWhereRaw('lower(last_name || \' \' || first_name) like ?', ["%{$search}%"]);
        } else {
            $query->whereRaw('lower(first_name) like ?', ["%{$search}%"])
                ->orWhereRaw('lower(last_name) like ?', ["%{$search}%"])
                ->orWhereRaw('lower(CONCAT(first_name, " ", last_name)) like ?', ["%{$search}%"])
                ->orWhereRaw('lower(CONCAT(last_name, " ", first_name)) like ?', ["%{$search}%"]);
        }
    }

    protected function isAdmin()
    {
        return auth()->user()->isAdmin();
    }
}
