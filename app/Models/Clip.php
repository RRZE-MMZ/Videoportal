<?php

namespace App\Models;

use App\Events\ClipDeleting;
use App\Models\Collection as TCollection;
use App\Models\Traits\Accessable;
use App\Models\Traits\Assetable;
use App\Models\Traits\Documentable;
use App\Models\Traits\Presentable;
use App\Models\Traits\RecordsActivity;
use App\Models\Traits\Searchable;
use App\Models\Traits\Slugable;
use App\Observers\ClipObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @method static first()
 * @method static find(int $int)
 */
#[ObservedBy(ClipObserver::class)]
class Clip extends BaseModel
{
    use Accessable; // can have multiple acls
    use Assetable; // can have multiple assets
    use Documentable; // can have multiple documents
    use Presentable; // can have multiple presenters
    use RecordsActivity;
    use Searchable;
    use Slugable;

    protected $with = ['acls'];

    // Update series timestamps on clip update
    protected $touches = ['series'];

    // search columns for searchable trait
    protected array $searchable = ['title', 'description'];

    // hide clip password from OpenSearch index
    protected $hidden = ['password'];

    protected $dispatchesEvents = [
        'deleting' => ClipDeleting::class,
    ];

    protected $attributes = ['episode' => '1'];

    protected $casts = [
        'recording_date' => 'datetime:Y-m-d',
        'time_availability_start' => 'datetime',
        'time_availability_end' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($clip) {
            $semester = Semester::find($clip->attributes['semester_id'])->acronym;
            $clip->setSlugAttribute($clip->episode.'-'.$clip->title.'-'.$semester);
        });

        static::updating(function ($clip) {
            $semester = Semester::find($clip->attributes['semester_id'])->acronym;
            $clip->setSlugAttribute($clip->episode.'-'.$clip->title.'-'.$semester);
        });
    }

    /**
     * Clip routes should work with slug and with id to ensure backward compatibility
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $clip = $this->where('slug', $value)->first();
        if (is_null($clip)) {
            $clip = $this->where('id', (int) $value)->firstOrFail();
        }

        return $clip;
    }

    /**
     * Route key should be slugged instead of id
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * User relationship
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(TCollection::class);
    }

    /**
     * Series relationship
     */
    public function series(): BelongsTo
    {
        // a clip may not belong to a series
        return $this->belongsTo(Series::class)->withDefault();
    }

    /**
     *  A clip hat one semester
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     *  A clip belongs to an organization
     */
    public function organization(): BelongsTo
    {
        return $this->BelongsTo(Organization::class, 'organization_id', 'org_id');
    }

    /**
     * A clip belongs to an image
     */
    public function image(): BelongsTo
    {
        return $this->BelongsTo(Image::class);
    }

    /**
     * A clip has one language
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get all the clips's comments.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * A clip belongs to context
     */
    public function context(): BelongsTo
    {
        return $this->BelongsTo(Context::class);
    }

    /**
     * A clip belongs to type
     */
    public function type(): BelongsTo
    {
        return $this->BelongsTo(Type::class);
    }

    /**
     * Updates clip poster image on asset upload
     */
    public function updatePosterImage(): void
    {
        if (Storage::disk('thumbnails')->exists($this->id.'_poster.png')) {
            $path = Storage::disk('thumbnails')->putFile(
                'clip_'.$this->id,
                new File(Storage::disk('thumbnails')->path($this->id.'_poster.png'))
            );

            $this->posterImage = $path;
            $this->save();
        } else {
            $this->posterImage = null;
        }
        $this->save();
    }

    /**
     * Add tags to clip
     */
    public function addTags(Collection $tagsCollection): void
    {
        /*
         * Check for tags collection from post request.
         * The closure returns a tag model, where the model is either selected or created.
         * The tag model is synchronized with the clip tags.
         * In case the collection is empty assumed that clip has no tags and delete them
         */
        if ($tagsCollection->isNotEmpty()) {
            $this->tags()->sync($tagsCollection->map(function ($tagName) {
                return tap(Tag::firstOrCreate(['name' => $tagName]))->save();
            })->pluck('id'));
        } else {
            $this->tags()->detach();
        }
    }

    /**
     * Tags relationship
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'clip_tag')->withTimestamps();
    }

    public function livestream(): HasOne
    {
        return $this->hasOne(Livestream::class);
    }

    public function previousNextClipCollection(): Collection
    {
        if (auth()->user()?->can('edit-clips', $this)) {
            $clipsCollection = $this->series->clips()->orderBy('episode')->get();
        } else {
            $clipsCollection = $this->series->clips->filter(function ($clip) {
                return ($clip->hasAudioAsset() || $clip->hasVideoAsset() || $this->hasRecordingDateInPast())
                    && $clip->is_public;
            })->sortBy('episode')->values();
        }

        $clips = $clipsCollection;

        $currentClipIndex = $clips->search(function ($clip) {
            return $this->id == $clip->id;
        });

        return collect([
            'previousClip' => $currentClipIndex > 0 ? $clips->get($currentClipIndex - 1) : null,
            'nextClip' => $clips->count() > $currentClipIndex + 1 ? $clips->get($currentClipIndex + 1) : null,
        ]);
    }

    /*
     * Return next and previous Models based on current Model episode attribute
     */

    public function views(): int
    {
        return $this->assets()
            ->with(['viewCount' => function ($query) {
                $query->select('resourceid', DB::raw('SUM(counter) as total_views'))
                    ->groupBy('resourceid');
            }])
            ->get()
            ->sum(function ($asset) {
                // Access the aggregated 'total_views' value directly
                return $asset->viewCount->first()->total_views ?? 0;
            });
    }

    // Function to calculate total views for all assets' stats

    public function sumGeoLocationData()
    {
        return $this->assets()->with(['geoCount' => function ($query) {
            $query->select(
                'resourceid',
                DB::raw('SUM(world) AS total_world'),
                DB::raw('SUM(bavaria) AS total_bavaria'),
                DB::raw('SUM(germany) AS total_germany')
            )->groupBy('resourceid');
        }])
            ->get()
            ->flatMap(function ($asset) {
                return $asset->geoCount;
            })
            ->reduce(function ($carry, $item) {
                return ['total' => [
                    'total_world' => $carry['total_world'] + $item->total_world,
                    'total_bavaria' => $carry['total_bavaria'] + $item->total_bavaria,
                    'total_germany' => $carry['total_germany'] + $item->total_germany,
                ],
                ];
            }, ['total_world' => 0, 'total_bavaria' => 0, 'total_germany' => 0]);
    }

    // Method to sum all geo location data across assets associated with this clip

    public function sumViewsDataGroupedByMonth(): array
    {
        $data = $this->assets->flatMap(function ($asset) {
            // Return the collection of view counts grouped by 'doa' with their sums
            return $asset->viewCount->groupBy('doa')
                ->map(function ($doaGroup, $doa) {
                    // Return sum of 'counter' for this 'doa' group
                    return [$doa => $doaGroup->sum('counter')];
                })->all();
        });

        // Aggregate sums from all assets by 'doa'
        $groupedData = collect($data)->reduce(function ($carry, $item) {
            foreach ($item as $doa => $sum) {
                if (! isset($carry[$doa])) {
                    $carry[$doa] = 0;
                }
                $carry[$doa] += $sum;
            }

            return $carry;
        }, []);

        // Sort by 'doa' in descending order
        $sortedData = collect($groupedData)->sortByDesc(function ($value, $key) {
            return $key;
        })->all();

        return $sortedData;
    }

    public function sumGeoLocationDataGroupedByMonth(): array
    {
        $monthlyData = [];

        $assets = $this->assets()->with(['geoCount' => function ($query) {
            $query->select(
                'resourceid',
                'month',
                DB::raw('SUM(world) AS total_world'),
                DB::raw('SUM(bavaria) AS total_bavaria'),
                DB::raw('SUM(germany) AS total_germany')
            )->groupBy('resourceid', 'month')->orderBy('month', 'desc');
        }])
            ->get();
        foreach ($assets as $asset) {
            foreach ($asset->geoCount as $geo) {
                $month = Carbon::parse($geo->month)->format('Y - F');
                if (! isset($monthlyData[$month])) {
                    $monthlyData[$month] = ['total_world' => 0, 'total_bavaria' => 0, 'total_germany' => 0];
                }
                $monthlyData[$month]['total_world'] += $geo->total_world;
                $monthlyData[$month]['total_bavaria'] += $geo->total_bavaria;
                $monthlyData[$month]['total_germany'] += $geo->total_germany;
            }
        }
        krsort($monthlyData);

        // Aggregate totals
        $total = ['total_world' => 0, 'total_bavaria' => 0, 'total_germany' => 0];
        foreach ($monthlyData as $month => $data) {
            $total['total_world'] += $data['total_world'];
            $total['total_bavaria'] += $data['total_bavaria'];
            $total['total_germany'] += $data['total_germany'];
        }

        // Optionally, add the total as a separate entry if needed
        //        $monthlyData['Total'] = $total;

        // If you don't want to modify the original monthly data but still need the total,
        // you can return both separately
        return [
            'monthlyData' => $monthlyData,
            'total' => $total,
        ];
    }

    /**
     * A clip belongs to format
     */
    public function format(): BelongsTo
    {
        return $this->BelongsTo(Format::class);
    }

    /*
     * check whether a clip belongs to a series
     */
    public function isPartOfSeries(): bool
    {
        return ! is_null($this->series);
    }

    /*
     * Check whether a clip is in the past based on it's recording date
     */
    public function hasRecordingDateInPast(): bool
    {
        $yesterday = now()->subDay()->endOfDay();

        return $this->recording_date->lessThanOrEqualTo($yesterday);
    }

    /**
     *  Scope a query to only include public clips
     */
    public function scopePublic($query): mixed
    {
        return $query->where('is_public', 1);
    }

    public function scopeWithVideoAssets($query): mixed
    {
        return $query->where('has_video_assets', 1);
    }

    /**
     *  Scope a query to only include clips without series
     */
    public function scopeSingle($query): mixed
    {
        return $query->whereNull('series_id');
    }

    public function scopeWithSemester($query)
    {
        return $query->addSelect(
            [
                'semester' => Semester::select('name')
                    ->whereColumn('id', 'clips.semester_id')
                    ->take(1),
            ]
        );
    }

    public function scopeCurrentSemester($query)
    {
        return $query->whereHas('semester', function ($q) {
            $q->current();
        });
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => html_entity_decode(
                htmlspecialchars_decode(
                    html_entity_decode(html_entity_decode($value, ENT_NOQUOTES, 'UTF-8'))
                )
            )
        );
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
