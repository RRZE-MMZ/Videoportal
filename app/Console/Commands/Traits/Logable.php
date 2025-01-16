<?php

namespace App\Console\Commands\Traits;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Log;

trait Logable
{
    use InteractsWithIO;

    /**
     * Writes the message in the log file and in the console.
     *
     * @param  string  $message  The message to log.
     * @param  string  $level  The log level (e.g., 'info', 'error').
     * @param  string  $type  The output type: 'console', 'log', or 'all'.
     * @return bool Whether the operation was successful.
     */
    public function commandLog(string $message, string $level = 'info', string $type = 'all'): bool
    {
        $className = get_class($this);
        $logMessage = "[COMMAND]:{$className}: {$message}";

        if ($type === 'console') {
            $this->$level($logMessage);
        } elseif ($type === 'log') {
            Log::channel('commands_log')->$level($logMessage);
        } else {
            Log::channel('commands_log')->$level($logMessage);
            $this->$level($logMessage);
        }

        return true;
    }
}
