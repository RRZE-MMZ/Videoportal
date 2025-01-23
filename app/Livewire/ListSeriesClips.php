<?php

namespace App\Livewire;

use App\Models\Chapter;
use App\Models\Clip;
use App\Models\Semester;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ListSeriesClips extends Component
{
    use withPagination;

    public $series;

    public $search = '';

    public $dashBoardAction = false;

    public $sortField = 'episode';

    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'title'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        $search = trim(Str::lower($this->search));
        $series = $this->series;
        $clips = $this->queryClips($search)->get();
        $chapterIDs = $clips->pluck('chapter_id')
            ->unique()
            ->reject(function ($value) {
                return $value === null || $value === 0; // Exclude null and 0 values
            })
            ->flatten();
        $dashboardAction = true;
        $clipsWithoutChapter = null;
        $chapters = collect();
        $defaultChapter = null;
        $view = 'livewire.series-clips-list-without-chapters';
        if ($chapterIDs->count() > 0) {
            $chapters =
                Chapter::select('id', 'position', 'title', 'default')
                    ->whereIn('id', $chapterIDs)->orderBy('position')->get();
            $defaultChapter = ($chapters->filter(function ($chapter) {
                return $chapter->default;
            })->first()?->id) ?? '0';
            $clipsByChapter = $clips->groupBy('chapter_id');
            // order each to clip to his chapter after searching the clips
            $chapters->each(function ($chapter) use ($clipsByChapter) {
                $chapter->clips = $clipsByChapter->get($chapter->id, collect());
            });
            $clipsWithoutChapter = $clips
                ->filter(function ($clip) {
                    return is_null($clip->chapter_id) || $clip->chapter_id == '0'; // Filter clips without a chapter
                })->sortBy('episode'); // Group the filtered clips by episode
            $view = 'livewire.series-clips-list-with-chapters';
        }

        return view($view,
            compact('series', 'clips', 'chapters', 'dashboardAction', 'search',
                'clipsWithoutChapter', 'defaultChapter'));
    }

    protected function queryClips($search)
    {
        $query = Clip::query()
            ->select(['id', 'title', 'slug', 'episode', 'is_public', 'recording_date', 'chapter_id'])
            ->whereSeriesId($this->series->id)
            ->addSelect(
                [
                    'semester' => Semester::select('name')
                        ->whereColumn('id', 'clips.semester_id')
                        ->take(1),
                ]
            );
        if (! auth()->check() || auth()->user()->cannot('edit-series', $this->series)) {
            $query->Public()
                ->where(function (Builder $query) {
                    $query->has('assets')
                        ->orWhere('recording_date', '<', Carbon::yesterday())
                        ->orWhere('is_livestream', true);
                });
        }

        return $this->applySearchFilter($query, $search)->orderBy('episode', 'asc');
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

    protected function applyPresenterSearchFilter($query, $search): void
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
}
