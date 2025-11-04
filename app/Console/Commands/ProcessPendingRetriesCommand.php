<?php

namespace App\Console\Commands;

use App\Jobs\SendCustodianshipNotificationJob;
use App\Models\Delivery;
use Illuminate\Console\Command;

class ProcessPendingRetriesCommand extends Command
{
    protected $signature = 'custodianships:process-pending-retries';

    protected $description = 'Process deliveries ready for retry based on next_retry_at';

    public function handle(): int
    {
        $readyDeliveries = Delivery::query()
            ->with(['custodianship.user', 'recipient'])
            ->where('status', 'pending')
            ->whereNotNull('next_retry_at')
            ->whereNotNull('error_message')
            ->where('next_retry_at', '<', now()->toDateTimeString())
            ->get();

        if ($readyDeliveries->isEmpty()) {
            $this->info('No deliveries ready for retry.');

            return self::SUCCESS;
        }

        $readyDeliveries->each(function ($delivery) {
            $nextAttempt = $delivery->attempt_number + 1;

            SendCustodianshipNotificationJob::dispatch(
                $delivery->custodianship,
                $delivery->recipient,
                $nextAttempt
            );
        });

        $this->info("Dispatched {$readyDeliveries->count()} retry notifications.");

        return self::SUCCESS;
    }
}
