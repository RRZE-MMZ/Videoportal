<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;

enum OpencastLogoPosition: string
{
    use InvokableCases;

    case TR = 'TOP RIGHT';
    case TL = 'TOP LEFT';
    case BR = 'BOTTOM RIGHT';
    case BL = 'BOTTOM LEFT';
}
