<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\WhatsAppAutomationService;
use Illuminate\Console\Command;

class WhatsAppRunAutomationsCommand extends Command
{
    protected $signature = 'whatsapp:run-automations';
    protected $description = 'Execute scheduled WhatsApp automations (welcome, expiry reminders, renewal reminders, birthday greetings)';

    public function handle(): int
    {
        $this->info('Starting WhatsApp automations runner...');

        $summary = WhatsAppAutomationService::runAll();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Tenants Processed', $summary['tenants_processed']],
                ['Messages Dispatched', $summary['messages_dispatched']],
                ['Skipped / Throttled', $summary['skipped_count']],
                ['Errors Encountered', count($summary['errors'])],
            ]
        );

        if (! empty($summary['errors'])) {
            $this->error('Some automation errors occurred:');
            foreach ($summary['errors'] as $err) {
                $this->line("- {$err}");
            }
        } else {
            $this->info('All automations executed successfully.');
        }

        return Command::SUCCESS;
    }
}

