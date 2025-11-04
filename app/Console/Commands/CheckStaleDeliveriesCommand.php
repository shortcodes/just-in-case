<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use Illuminate\Console\Command;

class CheckStaleDeliveriesCommand extends Command
{
    protected $signature = 'custodianships:check-stale-deliveries';

    protected $description = 'Check for stale delivery records and mark them as failed';

    public function handle(): int
    {
        $staleDeliveries = Delivery::query()
            ->with(['custodianship'])
            ->stale()
            ->get();

        if ($staleDeliveries->isEmpty()) {
            $this->info('No stale deliveries found.');

            return self::SUCCESS;
        }

        $staleDeliveries->each(fn ($delivery) => $delivery->fail('Delivery stale: no response after timeout'));

        $this->info("Marked {$staleDeliveries->count()} stale deliveries.");

        return self::SUCCESS;
    }
}
