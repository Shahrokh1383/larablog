<?php

namespace Modules\Marketing\Commands;

use Illuminate\Console\Command;
use Modules\Marketing\Services\NewsletterService;

class SendWeeklyNewsletterCommand extends Command
{
    protected $signature = 'marketing:send-weekly-newsletter';
    protected $description = 'Send the weekly best posts newsletter to all active subscribers';

    public function handle(NewsletterService $newsletterService): int
    {
        $this->info('Dispatching weekly newsletter job...');
        $newsletterService->dispatchNewsletterJob(sendToAll: true);
        $this->info('Job dispatched successfully.');

        return Command::SUCCESS;
    }
}