<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Console\Command;

class ResendFailedNotificationsCommand extends Command
{
    protected $signature = 'notifications:resend-failed {--limit=100 : Maximum number of failed deliveries to resend}';

    protected $description = 'Resend failed custodianship notifications';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $failedDeliveries = Delivery::query()
            ->with(['custodianship.user', 'recipient'])
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($failedDeliveries->isEmpty()) {
            $this->info('No failed deliveries found.');

            return self::SUCCESS;
        }

        $this->info("Found {$failedDeliveries->count()} failed deliveries to resend.");

        $bar = $this->output->createProgressBar($failedDeliveries->count());
        $bar->start();

        $resent = 0;
        $skipped = 0;

        foreach ($failedDeliveries as $delivery) {
            if (! $delivery->custodianship || ! $delivery->recipient) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $delivery->update(['status' => 'pending']);

            $delivery->recipient->notify(
                new ExpiredCustodianshipNotification(
                    $delivery->custodianship,
                    $delivery->recipient
                )
            );

            $resent++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Resent {$resent} notifications.");

        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} deliveries due to missing custodianship or recipient.");
        }

        return self::SUCCESS;
    }
}
