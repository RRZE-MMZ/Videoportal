<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use Illuminate\Support\Str;

enum Acl: int
{
    use InvokableCases;

    case PUBLIC = 1;
    case PORTAL = 2;
    case PASSWORD = 3;
    case LMS = 4;
    case OTHER = 5;

    // lowercase state's name
    public function lower(): string
    {
        return Str::lower($this->name);
    }
}
