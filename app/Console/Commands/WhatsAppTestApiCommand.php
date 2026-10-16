<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Illuminate\Console\Command;

class WhatsAppTestApiCommand extends Command
{
    protected $signature = 'whatsapp:test-connection {--tenant= : Optional tenant ID to test}';
    protected $description = 'Test connection to Meta WhatsApp Cloud API using configured credentials';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $tenant = $tenantId ? Tenant::find($tenantId) : Tenant::first();

        $this->info('Testing Meta WhatsApp Cloud API connection'.($tenant ? " for tenant: {$tenant->name}" : ' with global config').'...');

        $api = new WhatsAppCloudApiService($tenant);

        if (! $api->isConfigured()) {
            $this->error('WhatsApp Cloud API Phone Number ID or Access Token is missing.');
            $this->line('Please configure WHATSAPP_PHONE_NUMBER_ID and WHATSAPP_ACCESS_TOKEN in .env or via WhatsApp Settings.');
            return Command::FAILURE;
        }

        $result = $api->testConnection();

        if ($result['success']) {
            $this->info('SUCCESS: '.$result['message']);
            if (! empty($result['data'])) {
                $data = $result['data'];
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Phone Number ID', $data['id'] ?? 'N/A'],
                        ['Display Name', $data['verified_name'] ?? 'N/A'],
                        ['Display Phone Number', $data['display_phone_number'] ?? 'N/A'],
                        ['Quality Rating', $data['quality_rating'] ?? 'N/A'],
                        ['Code Verification', $data['code_verification_status'] ?? 'N/A'],
                    ]
                );
            }
            return Command::SUCCESS;
        }

        $this->error('FAILED: '.$result['message']);
        if (! empty($result['error_code'])) {
            $this->line("Meta Error Code: {$result['error_code']}");
        }

        return Command::FAILURE;
    }
}

