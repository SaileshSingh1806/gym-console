<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\TenantMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMailableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        public ?int $tenantId,
        public string|array $to,
        public Mailable $mailable
    ) {}

    public function handle(): void
    {
        try {
            if ($this->tenantId) {
                $tenant = Tenant::find($this->tenantId);
                if ($tenant) {
                    TenantMailService::sendNow($tenant, $this->to, $this->mailable);

                    return;
                }
            }

            Mail::to($this->to)->send($this->mailable);
        } catch (\Throwable $e) {
            Log::warning('Background SendMailableJob failed: '.$e->getMessage());
        }
    }
}
