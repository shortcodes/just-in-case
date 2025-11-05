<?php

namespace App\Console\Commands;

use App\Jobs\SendCustodianshipNotificationJob;
use App\Models\Custodianship;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificationsExpiredCustodianshipsCommand extends Command
{
    protected $signature = 'notifications:expired-custodianships';

    protected $description = 'Process expired custodianships and send notifications to recipients';

    public function handle(): int
    {
        $expiredCustodianships = Custodianship::query()
            ->where('status', 'active')
            ->where('next_trigger_at', '<=', now())
            ->with(['recipients', 'message', 'media', 'user'])
            ->get();

        if ($expiredCustodianships->isEmpty()) {
            $this->info('No expired custodianships found.');

            return self::SUCCESS;
        }

        $expiredCustodianships->each(fn ($custodianship) => $this->processCustodianship($custodianship));

        $this->info("Processed {$expiredCustodianships->count()} custodianship(s).");

        return self::SUCCESS;
    }

    protected function processCustodianship(Custodianship $custodianship): void
    {
        DB::transaction(function () use ($custodianship) {
            $custodianship->recipients->each(fn ($recipient) => SendCustodianshipNotificationJob::dispatch($custodianship, $recipient));

            $custodianship->update(['status' => 'completed']);
        });
    }
}
