<?php

namespace App\Models\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

trait RecordsActivity
{
    public array $oldAttributes = [];

    //a  list for attributes to check in updated event
    public array $checkedAttributes = [
        'title', 'description', 'image_id', 'episode', 'name', 'organization_id', 'language_id', 'context_id',
        'format_id', 'type_id', 'password', 'owner_id',  'allow_comments', 'is_public', 'is_livestream',
        'academic_degree_id', 'first_name', 'last_name', 'username', 'email', 'title_en', 'title_de', 'is_published',
    ];

    public static function bootRecordsActivity(): void
    {
        foreach (self::recordableEvents() as $event) {
            if ($event === 'updated') {
                static::updating(function ($model) {
                    $model->oldAttributes = $model->getOriginal();
                });
            }
            static::$event(function ($model) use ($event) {

                $attributes = ($event === 'updated')
                    ? [
                        'before' => '',
                        'after' => '',
                    ]
                    : [
                        'before' => '',
                        'after' => $model->getOriginal(),
                    ];
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

    protected function activityChanges(): array
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
            : ['before' => [''], 'after' => ['']];
    }

    protected function activityDescription($description): string
    {
        return "{$description} ".strtolower(class_basename($this));
    }
}
