<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use Illuminate\Support\Str;

enum OpencastWorkflowState: string
{
    use InvokableCases;

    case INSTANTIATED = 'EVENTS.EVENTS.STATUS.PENDING';
    case RUNNING = 'EVENTS.EVENTS.STATUS.PROCESSING';
    case STOPPED = 'EVENTS.EVENTS.STATUS.PROCESSING_CANCELLED';
    case PAUSED = 'EVENTS.EVENTS.STATUS.PAUSED';
    case SUCCEEDED = 'EVENTS.EVENTS.STATUS.PROCESSED';
    case FAILED = 'EVENTS.EVENTS.STATUS.PROCESSING_FAILURE';
    case SCHEDULED = 'EVENTS.EVENTS.STATUS.SCHEDULED';
    case RECORDING = 'EVENTS.EVENTS.STATUS.RECORDING';
    case WAITING = 'EVENTS.EVENTS.STATUS.WAITING';

    case TRIMMING = 'EVENTS.EVENTS.STATUS.TRIMMING';

    // lowercase state's name
    public function lower(): string
    {
        return Str::lower($this->name);
    }
}
