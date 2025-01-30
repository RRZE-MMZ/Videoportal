<?php

namespace App\Models\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

trait RecordsActivity
{
    public array $oldAttributes = [];

    // a  list for attributes to check in updated event
    public array $checkedAttributes = [
        'title', 'description', 'image_id', 'episode', 'name', 'organization_id', 'language_id', 'context_id',
        'format_id', 'type_id', 'password', 'owner_id',  'allow_comments', 'is_public', 'is_livestream',
        'academic_degree_id', 'first_name', 'last_name', 'username', 'email', 'title_en', 'title_de', 'is_published',
    ];

    public static function bootRecordsActivity(): void
    {
        static::created(function ($model) {
            // pass a non-empty `after` so it doesn't get skipped
            $model->recordActivity(
                $model->activityDescription('created'),
                [
                    'before' => [],
                    'after' => $model->getAttributes(),
                ]
            );
        });

        static::deleted(function ($model) {
            // We consider "before" as the model’s attributes that got deleted
            $model->recordActivity(
                $model->activityDescription('deleted'),
                [
                    'before' => $model->getOriginal(),
                    'after' => [],
                ]
            );
        });

        // handle the other events (updated, deleted, etc.)
        foreach (self::recordableEvents() as $event) {
            if ($event === 'created') {
                // we already handled `created` above
                continue;
            }

            if ($event === 'updated') {
                static::updating(function ($model) {
                    $model->oldAttributes = $model->getOriginal();
                });
            }

            static::$event(function ($model) use ($event) {
                $attributes = ($event === 'updated')
                    ? ['before' => '', 'after' => '']
                    : ['before' => '', 'after' => $model->getOriginal()];

                $model->recordActivity($model->activityDescription($event, $attributes));
            });
        }
    }

    protected static function recordableEvents(): array
    {
        return (isset(static::$recordableEvents)) ? static::$recordableEvents : ['created', 'updated', 'deleted'];
    }

    public function recordActivity($description, array $changes = []): void
    {
        $user = (auth()->user()) ?? $this->owner;
        $changes = (empty($changes['before']) && empty($changes['after'])) ? $this->activityChanges() : $changes;

        // do not record if nothing changed
        if (empty($changes['before']) && empty($changes['after'])) {
            return;
        }
        if (! Cache::has('insert_smil_command')) {
            Activity::create([
                'user_id' => ($user?->id) ?? 0,
                'content_type' => lcfirst(class_basename(static::class)),
                'object_id' => $this->id,
                'change_message' => $description,
                'action_flag' => 1,
                'changes' => $changes,
                'user_real_name' => ($user?->getFullNameAttribute()) ?? 'CRONJOB',
            ]);
        }
    }

    public function activities(): Builder
    {
        return Activity::where('object_id', $this->id)->where('content_type', lcfirst(class_basename(static::class)));
    }

    public function activityChanges(): array
    {
        return ($this->wasChanged($this->checkedAttributes))
            ?
            [
                'before' => Arr::except(array_diff($this->oldAttributes, $this->getAttributes()), [
                    'updated_at', 'slug',
                ]),
                'after' => Arr::except($this->getChanges(), [
                    'updated_at', 'slug',
                ]),
            ]
            : ['before' => [], 'after' => []];
    }

    protected function activityDescription($description): string
    {
        return "{$description} ".strtolower(class_basename($this));
    }
}
