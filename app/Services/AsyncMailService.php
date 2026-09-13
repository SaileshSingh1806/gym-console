<?php

namespace App\Services;

use App\Jobs\SendMailableJob;
use App\Models\Tenant;
use Illuminate\Mail\Mailable;

class AsyncMailService
{
    /**
     * Dispatch an email asynchronously to the queue and immediately kick a non-blocking background worker.
     */
    public static function dispatch(Tenant|int|null $tenant, string|array $to, Mailable $mailable): void
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        // Push job to database queue
        dispatch(new SendMailableJob($tenantId, $to, $mailable));

        // Trigger detached background queue worker (non-blocking)
        self::kickWorker();
    }

    /**
     * Launch a detached, non-blocking queue worker process to process the queue in the background.
     */
    public static function kickWorker(): void
    {
        try {
            $artisan = base_path('artisan');
            $phpBinary = PHP_BINARY ?: 'php';

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B cmd /C \"\"{$phpBinary}\" \"{$artisan}\" queue:work --once --tries=2 > NUL 2>&1\"", 'r'));
            } else {
                exec("\"{$phpBinary}\" \"{$artisan}\" queue:work --once --tries=2 > /dev/null 2>&1 &");
            }
        } catch (\Throwable) {
            // Ignore process spawn errors
        }
    }
}
