<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends BaseModel
{
    use HasFactory;
    use Searchable;

    // search columns for searchable trait
    protected array $searchable = ['content_type', 'change_message', 'user_real_name', 'changes'];

    protected $casts = [
        'changes' => 'array',
    ];
}
