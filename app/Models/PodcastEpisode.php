<?php

namespace App\Models;

use App\Models\Traits\Assetable;
use App\Models\Traits\Presentable;
use App\Models\Traits\RecordsActivity;
use App\Models\Traits\Searchable;
use App\Models\Traits\Slugable;
use App\Models\Traits\Taggable;
use App\Observers\PodcastEpisodeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

#[ObservedBy(PodcastEpisodeObserver::class)]
class PodcastEpisode extends BaseModel
{
    use Assetable;
    use HasFactory;
    use Presentable;
    use RecordsActivity;
    use Searchable;
    use Slugable;
    use Taggable;

    protected $touches = ['podcast'];

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $episode = $this->where('slug', $value)->first();
        if (is_null($episode)) {
            $episode = $this->where('id', (int) $value)->firstOrFail();
        }

        return $episode;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function previousNextEpisodeCollection(): Collection
    {
        $episodesCollection = $this->podcast->episodes()->orderBy('episode_number')->get();

        return collect([
            'previous' => $episodesCollection->filter(function ($value, $key) {
                return (int) $value->episode_number == (int) $this->episode_number - 1;
            })->first(),
            'next' => $episodesCollection->filter(function ($value, $key) {
                return (int) $value->episode_number == (int) $this->episode_number + 1;
            })->first(),
        ]);
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

    protected function transcription(): Attribute
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
