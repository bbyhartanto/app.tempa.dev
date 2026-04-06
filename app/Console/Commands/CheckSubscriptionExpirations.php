<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class CheckSubscriptionExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and process expired subscriptions and trials';

    protected SubscriptionService $subscriptionService;

    /**
     * Create a new command instance.
     */
    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking subscription expirations...');

        // Process expired subscriptions
        $expiredCount = $this->subscriptionService->processExpiredSubscriptions();
        if ($expiredCount > 0) {
            $this->info("✓ Processed {$expiredCount} expired subscription(s).");
        } else {
            $this->info('✓ No expired subscriptions to process.');
        }

        // Process expired trials
        $trialCount = $this->subscriptionService->processExpiredTrials();
        if ($trialCount > 0) {
            $this->info("✓ Expired {$trialCount} trial(s).");
        } else {
            $this->info('✓ No expired trials to process.');
        }

        $this->info('Subscription expiration check completed!');
        
        return Command::SUCCESS;
    }
}
