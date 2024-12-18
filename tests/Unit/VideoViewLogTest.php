<?php

use App\Models\Stats\AssetViewLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

uses()->group('unit');

test('to array', function () {
    $viewLog = AssetViewLog::factory()->create();

    expect(array_keys($viewLog->toArray()))->toBe([
        'resource_id', 'service_id', 'access_date', 'access_time', 'remote_addr', 'remote_host', 'remote_user',
        'script_name',  'is_counted', 'created_at', 'is_valid', 'in_range', 'referer', 'query', 'is_akami', 'server',
        'range', 'response', 'real_ip', 'num_ip', 'last_modified_at', 'last_modified_from', 'bot_name', 'city',
        'country', 'counter3', 'is_get', 'is_bot', 'region', 'region_name', 'log_id',
    ]);
});
it('belongs to an asset', function () {
    expect(AssetViewLog::factory()->create()->asset())->toBeInstanceOf(BelongsTo::class);
});
