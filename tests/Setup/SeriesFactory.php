<?php

namespace Tests\Setup;

use App\Enums\Content;
use App\Models\Asset;
use App\Models\Chapter;
use App\Models\Clip;
use App\Models\Semester;
use App\Models\Series;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;

class SeriesFactory
{
    use WithFaker;

    protected int $clipsCount = 0;

    protected int $assetsCount = 0;

    protected int $chaptersCount = 0;

    protected bool $opencastSeriesID = false;

    protected bool $isPublic = true;

    protected User $user;

    /**
     * @return $this
     */
    public function withClips($count): static
    {
        $this->clipsCount = $count;

        return $this;
    }

    /**
     * @return $this
     */
    public function withOpencastID(bool $opencastSeriesID = true): static
    {
        $this->opencastSeriesID = $opencastSeriesID;

        return $this;
    }

    /**
     * @return $this
     */
    public function notPublic(bool $isPublic = false): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return $this
     */
    public function withAssets($count): static
    {
        $this->assetsCount = $count;

        return $this;
    }

    public function withChapters($count): static
    {
        $this->chaptersCount = $count;

        return $this;
    }

    public function ownedBy($user): static
    {
        $this->user = $user;

        return $this;
    }

    public function create($count = 1): Series|Collection
    {
        // use ramsey/uuid because faker shows an error

        if ($count > 1) {
            $series = Series::factory($count)->create([
                'owner_id' => $user = $this->user ?? User::factory(),
                'opencast_series_id' => ($this->opencastSeriesID) ? Uuid::uuid1()->toString() : '',
                'is_public' => $this->isPublic,
            ]);

            $series->each(function ($series) use ($user) {
                if ($this->clipsCount > 0) {
                    Clip::factory($this->clipsCount)->sequence(fn ($sequence) => ['episode' => $sequence->index + 1])
                        ->create([
                            'series_id' => $series->id,
                            'owner_id' => $user,
                            'language_id' => Arr::random([1, 2]),
                            'semester_id' => Semester::current()->first()->id,
                            'image_id' => $series->image_id,
                        ]);

                    if ($this->assetsCount > 0) {
                        $series->clips()->each(function ($clip) {
                            $assets = Asset::factory($this->assetsCount)->create();
                            $assets->each(function ($asset) use ($clip) {
                                $clip->addAsset($asset);
                            });
                        });
                    }
                }
            });
        } else {
            $series = Series::factory()->create([
                'owner_id' => $user = $this->user ?? User::factory(),
                'opencast_series_id' => ($this->opencastSeriesID) ? Uuid::uuid1()->toString() : '',
                'is_public' => $this->isPublic,
            ]);

            if ($this->chaptersCount > 0) {
                Chapter::factory($this->chaptersCount)->create(['series_id' => $series->id]);
            }
            if ($this->clipsCount > 0) {
                Clip::factory($this->clipsCount)->create([
                    'series_id' => $series->id,
                    'owner_id' => $user,
                    'language_id' => Arr::random([1, 2]),
                    'semester_id' => Semester::current()->get()->first()->id,
                    'image_id' => $series->image_id,
                    'chapter_id' => $series->chapters()->first(),
                ]);

                if ($this->assetsCount > 0) {
                    $series->clips()->each(function ($clip) {
                        $assets = Asset::factory($this->assetsCount)->create();
                        $assets->add(Asset::factory()->create([
                            'type' => Content::SMIL,
                            'original_file_name' => 'composite.smil',
                        ]));
                        $assets->each(function ($asset) use ($clip) {
                            $clip->addAsset($asset);
                        });
                    });
                }
            }
        }

        return $series;
    }
}
