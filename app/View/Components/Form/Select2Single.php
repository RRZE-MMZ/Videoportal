<?php

namespace App\View\Components\Form;

use App\Models\AcademicDegree;
use App\Models\Chapter;
use App\Models\Context;
use App\Models\DeviceLocation;
use App\Models\Format;
use App\Models\Language;
use App\Models\Livestream;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Type;
use Illuminate\View\Component;
use Illuminate\View\View;

class Select2Single extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public ?string $model,
        public string $label,
        public string $fieldName,
        public $selectClass,
        public $selectedItem,
        public $columns = 8,
        public $columnStart = 2,
        public $columnEnd = 6,
        public $whereID = null,
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.form.select2-single', [
            'items' => match ($this->model) {
                'semester' => Semester::where('id', '>', 1)
                    ->orderBy('id', 'desc')
                    ->get(),
                'language' => Language::select(['id', 'code as name'])->where('id', '>', 0)->get(),
                'livestream' => Livestream::select(['id', 'name'])->where('active', false)->get(),
                'location' => DeviceLocation::select(['id', 'name'])->get(),
                'context' => Context::select(['id', 'de_name as name'])->get(),
                'format' => Format::select(['id', 'de_name as name'])->get(),
                'type' => Type::select(['id', 'de_name as name'])->get(),
                'academicDegree' => AcademicDegree::select(['id', 'title as name'])->get(),
                'role' => Role::where('id', '>', 0)
                    ->orderBy('id', 'desc')
                    ->get(),
                'organization' => Organization::select(['org_id as id', 'name'])
                    ->where('org_id', '=', $this->selectedItem)
                    ->get(),// make an api call. Therefor display only the selected option
                'chapter' => Chapter::select(['id', 'title as name'])
                    ->where('series_id', $this->whereID)->orderBy('position')->get(),
                'default' => []
            },
        ]);
    }

    /**
     * Determine if the given option is the currently selected option.
     *
     * @param  string  $option
     */
    public function isSelected($option): bool
    {
        return $option === $this->selectedItem;
    }
}
