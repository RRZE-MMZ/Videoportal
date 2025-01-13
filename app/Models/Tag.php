<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends BaseModel
{
    use Searchable;

    // search columns for searchable trait
    protected array $searchable = ['name'];

    /**
     * Clip relationship
     */
    public function clips(): BelongsToMany
    {
        return $this->belongsToMany(Clip::class, 'clip_tag')->withTimestamps();
    }
}
