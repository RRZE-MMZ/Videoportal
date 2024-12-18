<?php

use App\Models\Stats\AssetViewCount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

uses()->group('unit');

test('to array', function () {
    $viewCount = AssetViewCount::factory()->create();

    expect(array_keys($viewCount->toArray()))->toBe(['version', 'counter', 'doa', 'resourceid', 'serviceid', 'id']);
});

it('belongs to an asset', function () {
    expect(AssetViewCount::factory()->create()->asset())->toBeInstanceOf(BelongsTo::class);
});
