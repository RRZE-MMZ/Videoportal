<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends BaseModel
{
    use HasFactory;
    use Searchable;

    // search columns for searchable trait
    protected array $searchable = ['name'];

    protected $primaryKey = 'org_id';

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the clips for an organization unit
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class, 'organization_id');
    }

    /**
     * Get the series for an organization unit
     */
    public function series(): HasMany
    {
        return $this->hasMany(Series::class, 'organization_id');
    }

    public function scopeChairs($query)
    {
        return $query->where('parent_org_id', 1)->orWhere('org_id', 1);
    }
}
