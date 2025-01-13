<?php

namespace App\Models;

use App\Models\Traits\RecordsActivity;
use App\Models\Traits\Searchable;
use App\Observers\PresenterObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[ObservedBy(PresenterObserver::class)]
class Presenter extends BaseModel
{
    use HasFactory;
    use RecordsActivity;
    use Searchable;

    // search columns for searchable trait
    protected array $searchable = ['first_name', 'last_name', 'email', 'username'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getFullNameAttribute(): string
    {
        return ($this->academic_degree_id > 0)
            ? "{$this->academicDegree?->title} {$this->first_name} {$this->last_name}"
            : "{$this->first_name} {$this->last_name}";
    }

    /**
     * Series relationship
     */
    public function series(): MorphToMany
    {
        return $this->morphedByMany(Series::class, 'presentable')->withTimestamps();
    }

    /**
     * Clip relationship
     */
    public function clips(): MorphToMany
    {
        return $this->morphedByMany(Clip::class, 'presentable')->withTimestamps();
    }

    /*
     * A presenter has one  image
     */
    public function image(): BelongsTo
    {
        return $this->BelongsTo(Image::class);
    }

    public function clipsWithoutSeries(): Collection
    {
        return $this->clips->whereNull('series_id');
    }

    public function getImageUrl(): string
    {
        return (! is_null($this->image))
            ? env('app_url').'/images/'.$this->image?->file_name
            : env('app_url').'/images/DummyMann.png';
    }

    /**
     * Degree relationship
     */
    public function academicDegree(): BelongsTo
    {
        return $this->belongsTo(AcademicDegree::class);
    }
}
