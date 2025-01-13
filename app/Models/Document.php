<?php

namespace App\Models;

use App\Events\DocumentDeleted;
use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Document extends BaseModel
{
    use HasFactory;
    use RecordsActivity;

    // remove document from file disk on delete
    protected $dispatchesEvents = ['deleted' => DocumentDeleted::class];

    public function series(): MorphToMany
    {
        return $this->morphedByMany(Series::class, 'documentable');
    }

    public function clips(): MorphToMany
    {
        return $this->morphedByMany(Clip::class, 'documentable');
    }
}
