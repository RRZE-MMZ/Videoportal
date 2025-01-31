<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Models\Traits\RecordsActivity;
use App\Models\Traits\Searchable;
use App\Models\Traits\Slugable;
use App\Models\Traits\Taggable;
use App\Observers\PodcastsObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy(PodcastsObserver::class)]
class Podcast extends BaseModel
{
    use HasFactory;
    use Presentable;
    use RecordsActivity;
    use Searchable;
    use Slugable;
    use Taggable;

    protected array $searchable = ['title', 'description'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($podcast) {
            $podcast->setSlugAttribute($podcast->title);
        });
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $podcast = $this->where('slug', $value)->first();
        if (is_null($podcast)) {
            $podcast = $this->where('id', (int) $value)->firstOrFail();
        }

        return $podcast;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Fetch the latest clip from a series based on episode
     */
    public function latestEpisode(): HasOne
    {
        return $this->hasOne(PodcastEpisode::class)->latestOfMany('episode_number');
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => html_entity_decode(
                htmlspecialchars_decode(
                    html_entity_decode(html_entity_decode($value, ENT_NOQUOTES, 'UTF-8'))
                )
            )
        );
    }
}
