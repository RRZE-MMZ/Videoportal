<?php

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function () {
    $notification = Notification::factory()->create()->fresh();

    expect(array_keys($notification->toArray()))->toBe([
        'id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at',
    ]);
});

it('has an users method for model', function () {
    expect(Notification::factory()->create()->users())->toBeInstanceOf(BelongsTo::class);
});
