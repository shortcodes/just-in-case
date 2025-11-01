<?php

namespace App\Console\Commands;

use App\Models\Custodianship;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificationsExpiredCustodianshipsCommand extends Command
{
    protected $signature = 'notifications:expired-custodianships';

    protected $description = 'Process expired custodianships and send notifications to recipients';

    public function handle(): int
    {
        $this->validateConfiguration();

        $expiredCustodianships = $this->getExpiredCustodianships();

        if ($expiredCustodianships->isEmpty()) {
            $this->info('No expired custodianships found.');

            return self::SUCCESS;
        }

        $totalNotifications = 0;

        foreach ($expiredCustodianships as $custodianship) {
            $notificationsQueued = $this->processCustodianship($custodianship);
            $totalNotifications += $notificationsQueued;
        }

        $this->info("Processed {$expiredCustodianships->count()} custodianship(s), queued {$totalNotifications} notification(s).");

        return self::SUCCESS;
    }

    protected function validateConfiguration(): void
    {
        if (config('mail.mailers.mailgun.domain') === null && config('mail.default') === 'mailgun') {
            $this->error('Mailgun configuration is missing. Please check your environment variables.');
            exit(1);
        }
    }

    protected function getExpiredCustodianships()
    {
        return Custodianship::query()
            ->where('status', 'active')
            ->where('next_trigger_at', '<=', now())
            ->with(['recipients', 'message', 'media', 'user'])
            ->get();
    }

    protected function processCustodianship(Custodianship $custodianship): int
    {
        $notificationsQueued = 0;

        DB::transaction(function () use ($custodianship, &$notificationsQueued) {
            foreach ($custodianship->recipients as $recipient) {
                $recipient->notify(new ExpiredCustodianshipNotification(
                    $custodianship,
                    $recipient
                ));

                $notificationsQueued++;
            }

            $custodianship->update(['status' => 'completed']);
        });

        return $notificationsQueued;
    }
}
